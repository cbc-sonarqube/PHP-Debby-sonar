<?php

require_once(__DIR__.'/vendor/autoload.php');

use alsvanzelf\debby;

$options = [
	'root_dir'       => '/path/to/project/',
	'notify_github'  => [
		'repository' => 'example/project',
		'token'      => 'user token',
	],
	'notify_email'     => [
		'recipient' => 'devops@example.com',
		'host'      => 'smtp.example.com',
		'port'      => 587,
		'security'  => 'ssl',
		'user'      => 'devops@example.com',
		'pass'      => 'password',
	],
];
$debby = new debby\debby($options);

$results = $debby->check();
$debby->notify($results);
