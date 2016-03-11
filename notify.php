<?php

require_once(__DIR__.'/vendor/autoload.php');

use alsvanzelf\debby;

if (empty($argv[1])) {
	$e = new debby\exception('missing required argument notify address or path to options.json');
	$e->stop();
}

$options = [
	'notify_address' => $argv[1],
];
if (file_exists($argv[1]) === true) {
	$options = json_decode(file_get_contents($argv[1]), true);
	
	if ($options === null) {
		$error_code    = json_last_error();
		$error_message = debby\exception::get_json_error_message($error_code);
		
		$e = new debby\exception('unable to read options.json, "'.$error_message.'"', $error_code);
		$e->stop();
	}
}

$debby = new debby\debby($options);

$results = $debby->check();
$debby->notify($results);

exit(0);
