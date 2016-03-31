<?php

namespace alsvanzelf\debby\package;

use alsvanzelf\debby\exception;
use alsvanzelf\debby\manager;

abstract class abstract_package implements package {

protected $manager;
protected $name;
protected $cache_key;
protected $required_version;
protected $installed_version;
protected $latest_version;

/**
 * instantiate a new package
 * 
 * @param manager\manager $manager
 * @param string          $name
 */
public function __construct(manager\manager $manager, $name) {
	$this->manager = $manager;
	$this->name    = $name;
}

/**
 * get the package name for usage in notifications
 * 
 * @return string
 */
public function get_name() {
	return $this->name;
}

/**
 * get the name of the manager for usage in notifications
 * 
 * @return string
 */
public function get_manager_name() {
	return $this->manager->get_name();
}

/**
 * get a unique key for caching
 * 
 * @return string
 */
public function get_cache_key() {
	if (is_null($this->cache_key)) {
		$this->cache_key = $this->get_manager_name().':'.$this->get_name();
	}
	
	return $this->cache_key;
}

/**
 * get defined/required version
 * 
 * @return string
 */
public function get_required_version() {
	return $this->required_version;
}

/**
 * get currently installed version
 * 
 * @return string
 */
public function get_installed_version() {
	return $this->installed_version;
}

/**
 * get latest released version
 * 
 * @return string
 */
public function get_latest_version() {
	return $this->latest_version;
}

/**
 * check if the given $new_version, is newer than in the currently installed version
 * 
 * @param  string  $new_version
 * @return boolean
 */
public function is_later_version($new_version) {
	return ($new_version > $this->get_installed_version());
}

/**
 * whether the package knows a latest version newer than the currently installed version
 * 
 * @return boolean
 */
public function has_update() {
	return ($this->latest_version !== null);
}

/**
 * mark the package as required at given $version
 * 
 * @param  string $version
 * @return void
 */
public function mark_required($version) {
	$this->required_version = $version;
}

/**
 * mark the package as installed at given $version
 * 
 * @param  string $version
 * @return void
 */
public function mark_installed($version) {
	$this->installed_version = $version;
}

/**
 * mark the package as updatable with given $version
 * 
 * @param  string $version
 * @return void
 */
public function mark_updatable($version) {
	if ($this->is_later_version($version) === false) {
		$e = new exception('can not mark as updatable if the version is not newer than currently installed version');
		$e->stop();
	}
	
	$this->latest_version = $version;
}

}
