<?php

require_once(__DIR__.'/vendor/autoload.php');

use alsvanzelf\debby;

$options = [
	'notify_address' => 'webmaster@alsvanzelf.nl',
	'root_dir'       => '/path/to/project/',
];
$debby = new debby\debby($options);

$results = $debby->check();
$debby->notify($results);
