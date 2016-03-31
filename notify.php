<?php

require_once(__DIR__.'/../../autoload.php');

use alsvanzelf\debby;

if (empty($argv[1])) {
	$e = new debby\exception('missing required argument github repository or path to options.json');
	$e->stop();
}

// read from options.json file
if (strpos($argv[1], '.json')) {
	if (file_exists($argv[1]) === false) {
		$e = new debby\exception('options file not found at '.realpath($argv[1]));
		$e->stop();
	}
	
	$options = json_decode(file_get_contents($argv[1]), true);
	
	if ($options === null) {
		$error_code    = json_last_error();
		$error_message = debby\exception::get_json_error_message($error_code);
		
		$e = new debby\exception('unable to read options.json, "'.$error_message.'"', $error_code);
		$e->stop();
	}
}

// out of the box: creating github issues
elseif (strpos($argv[1], '/')) {
	if (empty($argv[2])) {
		$e = new debby\exception('missing required argument github token');
		$e->stop();
	}
	
	$options = [
		'notify_github' => [
			'repository' => $argv[1],
			'token'      => $argv[2],
		],
	];
}

// no other out-of-the-box uses
else {
	$e = new debby\exception('unknown notify request, supply github repository with token or options.json');
	$e->stop();
}

// ignite
$debby = new debby\debby($options);

$packages = $debby->check();
$notified = $debby->notify($packages);

// give feedback when testing it manually via cli
if (isset($_SERVER['TERM'])) {
	echo (empty($packages)) ? 'No updates found'.PHP_EOL : count($packages).' updates found'.PHP_EOL;
	
	if ($notified !== count($packages)) {
		echo (count($packages) - $notified).' not notified again'.PHP_EOL;
	}
}

exit(0);
