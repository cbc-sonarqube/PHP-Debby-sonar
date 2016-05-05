<?php

namespace alsvanzelf\debby\package;

use alsvanzelf\debby\manager;

class npm extends abstract_package {

/**
 * instantiate a new package
 * 
 * @param manager\manager $manager should be manager\npm
 * @param string          $name    in vendor/package format, i.e. 'alsvanzelf/debby'
 */
public function __construct(manager\manager $manager, $name) {
	if ($manager instanceof manager\npm === false) {
		$e = new exception('npm packages should be from npm manager');
		$e->stop();
	}
	
	parent::__construct($manager, $name);
}

}
