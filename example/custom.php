<?php

require_once(__DIR__.'/vendor/autoload.php');

use alsvanzelf\debby;

$options = [
	'cache_file' => './debby.cache',
	'check_composer' => [
		'path' => '/path/to/composerjson/',
	],
	'notify_github' => [
		'repository' => 'example/project',
		'token'      => 'user token',
	],
	'notify_trello' => [
		'list'  => 'list id',
		'token' => 'user token',
	],
	'notify_slack' => [
		'webhook' => 'https://hooks.slack.com/services/T00000000/B00000000/XXXXXXXXXXXXXXXXXXXXXXXX',
	],
	'notify_email' => [
		'recipient' => 'devops@example.com',
		'host'      => 'smtp.example.com',
		'port'      => 587,
		'security'  => 'ssl',
		'user'      => 'devops@example.com',
		'pass'      => 'password',
	],
];
$debby = new debby\debby($options);

$packages = $debby->check();
$debby->notify($packages);
