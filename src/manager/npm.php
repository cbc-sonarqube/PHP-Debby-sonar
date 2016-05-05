<?php

namespace alsvanzelf\debby\manager;

use alsvanzelf\debby;
use alsvanzelf\debby\exception;
use alsvanzelf\debby\package;

class npm implements manager {

private $path;
private $packages;
private $required;
private $installed;
private $updatable;

/**
 * setup the environment
 * 
 * @param array $options {
 *              @var $path where the package.json is placed
 * }
 */
public function __construct(array $options=[]) {
	if (empty($options['path'])) {
		$e = new exception('can not check for npm updates without a path option');
		$e->stop();
	}
	
	$this->path = $options['path'];
}

/**
 * manager name for usage in notifications
 * 
 * @return string
 */
public function get_name() {
	return 'npm';
}

/**
 * get a package object for the given name
 * 
 * this will always succeed, if one doesn't exist, one will be created
 * 
 * @param  string           $package_name in vendor/package format, i.e. 'alsvanzelf/debby'
 * @return package\npm
 */
public function get_package_by_name($package_name) {
	if (empty($this->packages[$package_name])) {
		$this->packages[$package_name] = new package\npm($this, $package_name);
	}
	
	return $this->packages[$package_name];
}

/**
 * give a list of all required packages
 * 
 * @return array<package\npm>
 */
public function find_required_packages() {
	if ($this->required === null) {
		if (debby\VERBOSE) {
			debby\debby::log('Checking '.$this->get_name().' for required packages');
		}
		
		if (file_exists($this->path.'package.json') === false) {
			$e = new exception('can not find package.json in "'.$this->path.'"');
			$e->stop();
		}
		
		$package_json = file_get_contents($this->path.'package.json');
		$package_json = json_decode($package_json, true);
		if (empty($package_json['devDependencies'])) {
			$e = new exception('there are no required packages to check');
			$e->stop();
		}
		
		$this->required = [];
		foreach ($package_json['devDependencies'] as $package_name => $required_version) {
			$package = $this->get_package_by_name($package_name);
			$package->mark_required($required_version);
			
			$this->required[$package->get_name()] = $package;
		}
	}
	
	return array_values($this->required);
}

/**
 * give a list of all installed packages
 * 
 * @return array<package\npm>
 */
public function find_installed_packages() {
	if ($this->installed === null) {
		if (debby\VERBOSE) {
			debby\debby::log('Checking '.$this->get_name().' for installed packages');
		}
		
		// get all current installed packages
		$installed_packages = shell_exec('cd '.$this->path.' && npm list --dev --depth=0 --json');
		$installed_packages = json_decode($installed_packages, true);
		if ($installed_packages === null) {
			$error_code    = json_last_error();
			$error_message = exception::get_json_error_message($error_code);
			
			$e = new exception('unable to read list of installed npm packages, "'.$error_message.'"', $error_code);
			$e->stop();
		}
		
		$this->installed = [];
		if (empty($installed_packages['dependencies'])) {
			return $this->installed;
		}
		
		foreach ($installed_packages['dependencies'] as $package_name => $package_info) {
			$package = $this->get_package_by_name($package_name);
			$package->mark_installed($package_info['version']);
			
			$this->installed[$package->get_name()] = $package;
		}
	}
	
	return array_values($this->installed);
}

/**
 * give a list of all updatable packages
 * 
 * @note this checks for the latest available version, not the 'wanted' version
 *       this helps in keeping up-to-date about new releases
 * 
 * @return array<package\npm>
 */
public function find_updatable_packages() {
	if ($this->updatable === null) {
		/**
		 * @todo don't call installed_packages here
		 *       instead, make calls to installed_version find out if there are installed
		 *       and let them cache the composer.lock themselves
		 */
		$this->find_required_packages();
		$this->find_installed_packages();
		
		if (debby\VERBOSE) {
			debby\debby::log('Checking '.$this->get_name().' for updatable packages');
		}
		
		// get all current outdated packages
		$updatable_packages = shell_exec('cd '.$this->path.' && npm outdated --json');
		$updatable_packages = json_decode($updatable_packages, true);
		if ($updatable_packages === null) {
			$error_code    = json_last_error();
			$error_message = exception::get_json_error_message($error_code);
			
			$e = new exception('unable to read list of updatable npm packages, "'.$error_message.'"', $error_code);
			$e->stop();
		}
		
		$this->updatable = [];
		if (empty($updatable_packages)) {
			return $this->updatable;
		}
		
		if (debby\VERBOSE) {
			$debug_index   = 0;
			$debug_count   = count($updatable_packages);
			$debug_padding = strlen($debug_count);
		}
		
		foreach ($updatable_packages as $package_name => $package_info) {
			$package = $this->get_package_by_name($package_name);
			if (debby\VERBOSE) {
				$debug_index++;
				$debug_prefix = "\t".str_pad($debug_index, $debug_padding, $string=' ', $type=STR_PAD_LEFT).'/'.$debug_count.': ';
				debby\debby::log($debug_prefix.$package->get_name());
			}
			
			if ($package->is_later_version($package_info['latest'])) {
				$package->mark_updatable($package_info['latest']);
				$this->updatable[$package->get_name()] = $package;
			}
		}
	}
	
	return array_values($this->updatable);
}

}
