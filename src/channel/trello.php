<?php

namespace alsvanzelf\debby\channel;

use alsvanzelf\debby;
use alsvanzelf\debby\exception;
use alsvanzelf\debby\package;
use alsvanzelf\debby\template;

class trello implements channel {

private $client;
private $token;
private $list;

private static $api_key = '9b174ff1ccf5ca94f1c181bc3d802d4b';

/**
 * setup a connection with trello
 * 
 * @param array $options {
 *              @var $list  list to add cards to
 *              @var $token personal (developer) token
 * }
 */
public function __construct(array $options=[]) {
	if (empty($options['token']) || empty($options['list'])) {
		$e = new exception('trello notifications require an api key and token, and a list option');
		$e->stop();
	}
	
	$this->token  = $options['token'];
	$this->list   = $options['list'];
	$this->client = curl_init('https://api.trello.com/1/cards');
	
	$curl_options = [
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_SSL_VERIFYPEER => true,
		CURLOPT_SSL_VERIFYHOST => 2,
		CURLOPT_HTTPHEADER     => [
			'Accept: application/json',
			'Content-Type: application/json; charset=utf-8',
			'User-Agent: Debby/'.debby\debby::VERSION.' (https://github.com/lode/debby)',
		],
	];
	
	curl_setopt_array($this->client, $curl_options);
}

/**
 * close the curl handle
 */
public function __destruct() {
	curl_close($this->client);
}

/**
 * create trello cards for each updatable package
 * 
 * @param  array<package> $packages as returned by debby->check()
 * @return void
 */
public function send(array $packages) {
	foreach ($packages as $package) {
		$this->send_single_package($package);
	}
}

/**
 * create trello card for a single package
 * 
 * @param  package $package
 * @return void
 */
private function send_single_package(package\package $package) {
	$template_data = [
		'name'      => $package->get_name(),
		'manager'   => $package->get_manager_name(),
		'required'  => $package->get_required_version(),
		'installed' => $package->get_installed_version(),
		'latest'    => $package->get_latest_version(),
	];
	
	$card_name        = 'Update '.$package->get_manager_name().' package '.$package->get_name();
	$card_description = template::parse('trello_single', $template_data);
	
	$this->create_card($card_name, $card_description);
}

/**
 * create an card
 * 
 * @param  string $name
 * @param  string $description
 * @return void
 */
private function create_card($name, $description) {
	$arguments = json_encode([
		'name'   => $name,
		'desc'   => $description,
		'idList' => $this->list,
		'pos'    => 'top',
		
		// authenticate
		'key'    => self::$api_key,
		'token'  => $this->token,
	]);
	
	$curl_options = [
		CURLOPT_POST       => true,
		CURLOPT_POSTFIELDS => $arguments,
	];
	curl_setopt_array($this->client, $curl_options);
	
	curl_exec($this->client);
	
	if (curl_errno($this->client)) {
		$e = new exception('trello curl error "'.curl_error($this->client).'"');
		$e->stop();
	}
}

}
