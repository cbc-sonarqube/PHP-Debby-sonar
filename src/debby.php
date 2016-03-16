<?php

namespace alsvanzelf\debby;

class debby {

private $options;

/**
 * make changes to default behavior
 *
 * @param array $options {
 *        @var string $notify_address email address where notification will be sent to
 *                                    required when using ->notify()
 *        @var string $root_dir       root directory of the project
 *                                    optional, assumes debby is loaded via composer
 *        @var array  $smtp_login     required when using ->notify()
 * }
 */
public function __construct(array $options=array()) {
	self::arrange_environment();
	
	$this->options = $options;
	
	if (empty($this->options['root_dir'])) {
		$this->options['root_dir'] = realpath(__DIR__.'/../../../../').'/';
	}
}

/**
 * arrange a good environment for debugging and cli interaction
 * 
 * @return void
 */
protected static function arrange_environment() {
	ini_set('display_startup_errors', 1);
	ini_set('display_errors', 1);
	error_reporting(-1);
	
	$error_handler = function($severity_code, $message, $file, $line, $context) {
		$severity_type = exception::get_php_native_error_message($severity_code);
		
		$e = new exception('['.$severity_type.'] '.$message);
		$e->stop();
	};
	set_error_handler($error_handler);
	
	mb_internal_encoding('UTF-8');
	date_default_timezone_set('UTC');
	setlocale(LC_ALL, 'en_US.utf8', 'en_US', 'C.UTF-8');
}

/**
 * checks composer packages for new releases since the installed version
 * 
 * @todo check if the required version is significantly off
 *       which would mean the json needs to change to be able to update
 * 
 * @return array {
 *         @var $required
 *         @var $installed
 *         @var $possible
 * }
 */
public function check() {
	$composer_json = file_get_contents($this->options['root_dir'].'composer.json');
	$composer_json = json_decode($composer_json, true);
	if (empty($composer_json['require'])) {
		$e = new exception('there are no required packages to check');
		$e->stop();
	}
	
	$composer_lock = file_get_contents($this->options['root_dir'].'composer.lock');
	$composer_lock = json_decode($composer_lock, true);
	if (empty($composer_lock['packages'])) {
		$e = new exception('lock file is missing its packages');
		$e->stop();
	}
	
	$composer_executable = 'composer';
	if (file_exists($this->options['root_dir'].'composer.phar')) {
		$composer_executable = 'php composer.phar';
	}
	
	$required_packages  = $composer_json['require'];
	$installed_packages = $composer_lock['packages'];
	$update_packages    = array();
	
	foreach ($installed_packages as $installed_package) {
		$package_name      = $installed_package['name'];
		$installed_version = preg_replace('/v([0-9].*)/', '$1', $installed_package['version']);
		$version_regex     = '/versions\s*:.+v?([0-9]+\.[0-9]+(\.[0-9]+)?)(,|$)/U';
		
		// skip dependencies of dependencies
		if (empty($required_packages[$package_name])) {
			continue;
		}
		
		// check commit hash for dev-* versions
		if (strpos($installed_version, 'dev-') === 0) {
			$installed_version = $installed_package['source']['reference'];
			$version_regex     = '/source\s*:.+ ([a-f0-9]{40})$/m';
		}
		
		// find out the newest release
		$package_info = shell_exec('cd '.$this->options['root_dir'].' && '.$composer_executable.' show -a '.escapeshellarg($package_name));
		preg_match($version_regex, $package_info, $possible_version);
		if (empty($possible_version)) {
			$e = new exception('can not find out newest release for '.$package_name);
			$e->stop();
		}
		
		if ($possible_version[1] == $installed_version) {
			continue;
		}
		
		// keep sha1 hashes short
		if (strlen($installed_version) === 40) {
			$installed_version = substr($installed_version, 0, 7);
		}
		if (strlen($possible_version[1]) === 40) {
			$possible_version[1] = substr($possible_version[1], 0, 7);
		}
		
		$update_packages[$package_name] = array(
			'required'  => $required_packages[$package_name],
			'installed' => $installed_version,
			'latest'    => $possible_version[1],
		);
	}
	
	return $update_packages;
}

/**
 * send an email with the results from ->check()
 * 
 * @note requires options with `notify_address` and `smtp_login` keys
 * 
 * @param  array $results output from ->check()
 * @return void
 */
public function notify(array $results) {
	if (empty($this->options['notify_address'])) {
		throw new exception('can not notify without email address of the recipient');
	}
	
	list($subject, $body) = (empty($results)) ? self::get_fine_email() : self::get_update_email($results);
	
	$message = new \Swift_Message();
	$message->setFrom($this->options['notify_address']);
	$message->setTo($this->options['notify_address']);
	$message->setSubject($subject);
	$message->setBody($body);
	
	$smtp_login = $this->options['smtp_login'];
	$transport = new \Swift_SmtpTransport($smtp_login['host'], $smtp_login['port'], $smtp_login['ssl']);
	$transport->setUsername($smtp_login['user']);
	$transport->setPassword($smtp_login['pass']);
	
	$mailer = new \Swift_Mailer($transport);
	$mailer->send($message);
}

/**
 * get the email subject and body when everything is up-to-date
 * 
 * @return array {
 *         @var string $subject
 *         @var string $body
 * }
 */
private static function get_fine_email() {
	return array(
		'All dependencies running fine',
		file_get_contents(__DIR__.'/templates/email_fine.txt'),
	);
}

/**
 * get the email subject and body when dependencies have updates
 * 
 * @param  array $results output from ->check()
 * 
 * @return array {
 *         @var string $subject
 *         @var string $body
 * }
 */
private static function get_update_email($results) {
	$template_package_line = file_get_contents(__DIR__.'/templates/email_package.txt');
	$template_whole_body   = file_get_contents(__DIR__.'/templates/email_updates.txt');
	
	$package_lines = '';
	foreach ($results as $package_name => $versions) {
		$package_lines .= self::fill_package_line_template($template_package_line, $package_name, $versions);
	}
	
	$subject = 'Dependency updates needed!';
	$body    = str_replace('{{packages}}', $package_lines, $template_whole_body);
	
	return array(
		$subject,
		$body,
	);
}

/**
 * fill a template with package name and version constraints
 * 
 * @param  string $template
 * @param  string $package_name
 * @param  array  $versions {
 *                @var string $required
 *                @var string $installed
 *                @var string $latest
 * }
 * 
 * @return string
 */
private static function fill_package_line_template($template, $package_name, $versions) {
	$package_line = str_replace('{{name}}', $package_name, $template);
	
	foreach ($versions as $version_key => $version_value) {
		$package_line = str_replace('{{'.$version_key.'}}', $version_value, $package_line);
	}
	
	return $package_line;
}

}
