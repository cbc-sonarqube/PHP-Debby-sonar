<?php

namespace alsvanzelf\debby\package;

use alsvanzelf\debby\manager;

class composer extends abstract_package {

protected $installed_reference;

/**
 * instantiate a new package
 * 
 * @param manager\manager $manager should be manager\composer
 * @param string          $name    in vendor/package format, i.e. 'alsvanzelf/debby'
 */
public function __construct(manager\manager $manager, $name) {
	if ($manager instanceof manager\composer === false) {
		$e = new exception('composer packages should be from composer manager');
		$e->stop();
	}
	
	parent::__construct($manager, $name);
}

/**
 * get currently installed version
 * 
 * @note if the installed version is a commit hash, the short variant is returned
 * 
 * @return string
 */
public function get_installed_version() {
	// keep sha1 hashes short
	if ($this->is_installed_by_reference()) {
		return self::get_short_hash($this->installed_reference);
	}
	
	return parent::get_installed_version();
}

/**
 * get latest released version
 * 
 * @note if the installed version is a commit hash, the short variant is returned
 * 
 * @todo check if the required version is significantly off
 *       which would mean the json needs to change to be able to update
 * 
 * @return string
 */
public function get_latest_version() {
	// keep sha1 hashes short
	if (strlen($this->latest_version) === 40) {
		return self::get_short_hash($this->latest_version);
	}
	
	return $this->latest_version;
}

/**
 * check if the given $new_version, is newer than in the currently installed version
 * 
 * currently doesn't check `if newer`, but `if different`
 * in practice this is the same, as the installed can not be newer than the latest version
 * 
 * @note if the given $new_version is a commit hash, the short variant is used
 * 
 * @param  string  $new_version
 * @return boolean
 */
public function is_later_version($new_version) {
	// keep sha1 hashes short
	if (strlen($new_version) === 40) {
		$new_version = self::get_short_hash($new_version);
	}
	
	return ($new_version != $this->get_installed_version());
}

/**
 * whether the currently installed version is the latest commit from a branch-release
 * instead of a fixated commit from a tag
 * 
 * @return boolean
 */
public function is_installed_by_reference() {
	return (bool) $this->installed_reference;
}

/**
 * mark the package as installed at given $version
 * 
 * @note strips off any `v`-prefix
 * 
 * @param  string $version
 * @return void
 */
public function mark_installed($version) {
	$version = preg_replace('/v([0-9].*)/', '$1', $version);
	
	return parent::mark_installed($version);
}

/**
 * mark the package as installed with the latest commit from a branch-release
 * instead of a fixated commit from a tag
 * 
 * @param  string $commit_hash
 * @return void
 */
public function mark_installed_by_reference($commit_hash) {
	$this->installed_reference = $commit_hash;
}

/**
 * returns the short variant of a commit hash
 * 
 * @param  string $full_hash 40 chars long
 * @return string            7 chars short
 */
private static function get_short_hash($full_hash) {
	return substr($full_hash, 0, 7);
}

}
