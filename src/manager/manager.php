<?php

namespace alsvanzelf\debby\manager;

interface manager {

/**
 * setup the environment
 * 
 * @param array $options {
 *              @var $root_dir
 * }
 */
public function __construct(array $options=[]);

/**
 * manager name for usage in notifications
 * 
 * @return string
 */
public function get_name();

/**
 * get a package object for the given name
 * 
 * this should always succeed, if one doesn't exist, one will be created
 * 
 * @param  string  $package_name
 * @return package
 */
public function get_package_by_name($package_name);

/**
 * give a list of all required packages
 * 
 * @return array<package>
 */
public function find_required_packages();

/**
 * give a list of all installed packages
 * 
 * @return array<package>
 */
public function find_installed_packages();

/**
 * give a list of all updatable packages
 * 
 * @return array<package>
 */
public function find_updatable_packages();

}
