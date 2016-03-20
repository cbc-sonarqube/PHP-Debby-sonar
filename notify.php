<?php

require_once(__DIR__.'/../../autoload.php');

use alsvanzelf\debby;

if (empty($argv[1])) {
	$e = new debby\exception('missing required argument github repository or path to options.json');
	$e->stop();
}

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
else {
	$e = new debby\exception('unknown notify request, supply github repository with token or options.json');
	$e->stop();
}

$debby = new debby\debby($options);

$packages = $debby->check();
$debby->notify($packages);

// give feedback when testing it manually via cli
if (isset($_SERVER['TERM'])) {
	echo (empty($packages)) ? 'No updates found'.PHP_EOL : count($packages).' updates found'.PHP_EOL;
}

exit(0);
