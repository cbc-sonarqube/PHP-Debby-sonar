<?php

namespace alsvanzelf\debby\channel;

use alsvanzelf\debby;
use alsvanzelf\debby\exception;
use alsvanzelf\debby\package;
use alsvanzelf\debby\template;

class slack implements channel {

private $client;

/**
 * setup a connection with slack
 * 
 * @param array $options {
 *              @var $webhook url to post messages to
 * }
 */
public function __construct(array $options=[]) {
	if (empty($options['webhook'])) {
		$e = new exception('slack notifications require a webhook option');
		$e->stop();
	}
	
	$this->client = curl_init($options['webhook']);
	
	$curl_options = [
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_SSL_VERIFYPEER => true,
		CURLOPT_SSL_VERIFYHOST => 2,
		CURLOPT_HTTPHEADER     => [
			'Content-type: application/json',
			'User-Agent: Debby/'.debby\debby::get_version().' (https://github.com/lode/debby)',
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
 * send a message for updatable packages
 * 
 * @param  array<package> $packages as returned by debby->check()
 * @return void
 */
public function send(array $packages) {
	if (count($packages) === 1) {
		$text = $this->get_package_message($packages[0], $template='slack_single');
	}
	else {
		$package_lines = '';
		foreach ($packages as $package) {
			$package_lines .= $this->get_package_message($package, $template='slack_multiple_line');
		}
		
		$text = template::parse('slack_multiple', ['packages' => $package_lines]);
	}
	
	$this->send_message($text);
}

/**
 * get a parsed message for a single package
 * 
 * @param  package $package
 * @param  string  $template one of 'slack_multiple_line' or 'slack_single'
 * @return string
 */
private function get_package_message(package\package $package, $template) {
	$template_data = [
		'name'      => $package->get_name(),
		'manager'   => $package->get_manager_name(),
		'required'  => $package->get_required_version(),
		'installed' => $package->get_installed_version(),
		'latest'    => $package->get_latest_version(),
	];
	
	return template::parse($template, $template_data);
}

/**
 * send a message to a channel
 * 
 * @param  string $text
 * @return void
 */
private function send_message($text) {
	$arguments = json_encode([
		'username'   => 'Debby',
		'icon_emoji' => ':girl::skin-tone-2:',
		'text'       => $text,
	]);
	
	$curl_options = [
		CURLOPT_POST       => true,
		CURLOPT_POSTFIELDS => $arguments,
	];
	curl_setopt_array($this->client, $curl_options);
	
	curl_exec($this->client);
	
	if (curl_errno($this->client)) {
		$e = new exception('slack curl error "'.curl_error($this->client).'"');
		$e->stop();
	}
}

}
