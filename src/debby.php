<?php

namespace alsvanzelf\debby;

use alsvanzelf\debby\manager;
use alsvanzelf\debby\channel;

class debby {

private $options;

private static $version;

/**
 * make changes to default behavior
 *
 * @param array $options {
 *        @var string $root_dir       root directory of the project
 *                                    optional, assumes debby is loaded via composer
 *        @var string $notify_github  create issues on github for package updates
 *        @var string $notify_slack   message package updates to a slack channel
 *        @var array  $notify_email   email package updates, this sends all in one
 * }
 */
public function __construct(array $options=[]) {
	self::arrange_environment();
	
	$this->options = $options;
	
	if (empty($this->options['root_dir'])) {
		$this->options['root_dir'] = realpath(__DIR__.'/../../../../').'/';
	}
}

/**
 * get debby's current version
 * useful for outgoing user-agents
 * 
 * @return string i.e. 'v0.7'
 */
public static function get_version() {
	if (empty(self::$version)) {
		$latest_tag    = shell_exec('git describe --abbrev=0 --tags');
		self::$version = trim($latest_tag);
	}
	
	return self::$version;
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
 * checks packages for new releases since the installed version
 * 
 * @return array<package>
 */
public function check() {
	$packages = [];
	
	$composer = new manager\composer($this->options);
	$packages += $composer->find_updatable_packages();
	
	return $packages;
}

/**
 * send the updatable packages to defined destinations
 * 
 * currently accepted via generic options:
 * - github: creates issues per result
 * - email: sends an email with all updatable packages
 * 
 * @param  array<package> $packages as returned by ->check()
 * 
 * @return void
 */
public function notify(array $packages) {
	if (empty($packages)) {
		return;
	}
	
	if (!empty($this->options['notify_github'])) {
		$github = new channel\github($this->options['notify_github']);
		$github->send($packages);
	}
	
	if (!empty($this->options['notify_slack'])) {
		$slack = new channel\slack($this->options['notify_slack']);
		$slack->send($packages);
	}
	
	if (!empty($this->options['notify_email'])) {
		$email = new channel\email($this->options['notify_email']);
		$email->send($packages);
	}
}

}
