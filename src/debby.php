<?php

namespace alsvanzelf\debby;

use alsvanzelf\debby\manager;
use alsvanzelf\debby\channel;

class debby {

private $options;
private $cache;

private static $version;

/**
 * make changes to default behavior
 *
 * @param array $options {
 *        @var string $root_dir       root directory of the project
 *                                    optional, assumes debby is loaded via composer
 *        @var string $cache_file     path of the cache file
 *                                    optional, defaults to `debby.cache` inside debby's vendor directory
 *        @var string $notify_github  create issues on github for package updates
 *        @var string $notify_trello  add cards in trello for package updates
 *        @var string $notify_slack   message package updates to a slack channel
 *        @var array  $notify_email   email package updates, this sends all in one
 * }
 */
public function __construct(array $options=[]) {
	self::arrange_environment();
	
	$this->options = $options;
	
	if (empty($this->options['cache_file'])) {
		$this->options['cache_file'] = realpath(__DIR__.'/..').'/debby.cache';
	}
	if (empty($this->options['root_dir'])) {
		$this->options['root_dir'] = realpath(__DIR__.'/../../../../').'/';
	}
	
	$this->cache = new cache($this->options['cache_file']);
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
 * - github: creates issues per package
 * - trello: add cards per package
 * - slack: sends messages for a single or multiple packages
 * - email: sends an email with all updatable packages
 * 
 * @note builds and checks a cache of packages notified before
 * 
 * @param  array<package> $packages as returned by ->check()
 * 
 * @return int            the amount of packages which have been notified
 *                        this is a count of the input, minus the earlier notified packages
 */
public function notify(array $packages) {
	// skip packages which have been notified before
	foreach ($packages as $index => $package) {
		if ($this->cache->contains($package->get_cache_key()) === false) {
			continue;
		}
		
		$cache = $this->cache->get($package->get_cache_key());
		if (empty($cache['latest_version']) || $cache['latest_version'] !== $package->get_latest_version()) {
			continue;
		}
		
		unset($packages[$index]);
	}
	
	if (empty($packages)) {
		return 0;
	}
	
	if (!empty($this->options['notify_github'])) {
		$github = new channel\github($this->options['notify_github']);
		$github->send($packages);
	}
	
	if (!empty($this->options['notify_trello'])) {
		$trello = new channel\trello($this->options['notify_trello']);
		$trello->send($packages);
	}
	
	if (!empty($this->options['notify_slack'])) {
		$slack = new channel\slack($this->options['notify_slack']);
		$slack->send($packages);
	}
	
	if (!empty($this->options['notify_email'])) {
		$email = new channel\email($this->options['notify_email']);
		$email->send($packages);
	}
	
	// cache the newly notified updates
	foreach ($packages as $package) {
		/**
		 * @todo cache results from channels like github issue id
		 */
		$cache_value = array(
			'latest_version' => $package->get_latest_version(),
			'notified'       => time(),
		);
		$this->cache->cache($package->get_cache_key(), $cache_value);
	}
	
	return count($packages);
}

}
