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

// auto turn on verbose mode when testing it manually via cli
if (isset($_SERVER['TERM']) && !isset($options['verbose'])) {
	$options['verbose'] = 1;
}

// ignite
$debby = new debby\debby($options);

$packages = $debby->check();

if (debby\VERBOSE) {
	$log_message = (empty($packages)) ? 'No updates found' : 'Found '.count($packages).' updates';
	debby\debby::log($log_message);
}

$notified = $debby->notify($packages);

if (debby\VERBOSE) {
	if ($notified !== count($packages)) {
		debby\debby::log('Skipped notification of '.(count($packages) - $notified).' packages');
	}
}

exit(0);
