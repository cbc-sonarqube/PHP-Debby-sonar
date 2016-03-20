<?php

namespace alsvanzelf\debby\package;

use alsvanzelf\debby\manager;

interface package {

/**
 * instantiate a new package
 * 
 * @param manager\manager $manager
 * @param string          $name
 */
public function __construct(manager\manager $manager, $name);

/**
 * get the package name for usage in notifications
 * 
 * @return string
 */
public function get_name();

/**
 * get the name of the manager for usage in notifications
 * 
 * @return string
 */
public function get_manager_name();

/**
 * get defined/required version
 * 
 * @return string
 */
public function get_required_version();

/**
 * get currently installed version
 * 
 * @return string
 */
public function get_installed_version();

/**
 * get latest released version
 * 
 * @return string
 */
public function get_latest_version();

/**
 * check if the given $new_version, is newer than in the currently installed version
 * 
 * @param  string  $new_version
 * @return boolean
 */
public function is_later_version($new_version);

/**
 * whether the package knows a latest version newer than the currently installed version
 * 
 * @return boolean
 */
public function has_update();

/**
 * mark the package as required at given $version
 * 
 * @param  string $version
 * @return void
 */
public function mark_required($version);

/**
 * mark the package as installed at given $version
 * 
 * @param  string $version
 * @return void
 */
public function mark_installed($version);

/**
 * mark the package as updatable with given $version
 * 
 * @param  string $version
 * @return void
 */
public function mark_updatable($version);

}
