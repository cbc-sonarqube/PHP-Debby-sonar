<?php

namespace alsvanzelf\debby\channel;

use alsvanzelf\debby\exception;
use alsvanzelf\debby\package;
use alsvanzelf\debby\template;

class github implements channel {

private $client;
private $repository;

/**
 * setup a connection with github
 * 
 * @param array $options {
 *              @var $repository repository to create issues at
 *              @var $token      personal access token with (private) repo access
 * }
 */
public function __construct(array $options=[]) {
	if (empty($options['token']) || empty($options['repository'])) {
		$e = new exception('github notifications require a token and a repository option');
		$e->stop();
	}
	
	$this->repository = $options['repository'];
	$this->client     = curl_init();
	
	$debby_version = shell_exec('git describe --abbrev=0 --tags');
	$curl_options = [
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_SSL_VERIFYPEER => true,
		CURLOPT_SSL_VERIFYHOST => 2,
		CURLOPT_HTTPHEADER     => [
			'Accept: application/vnd.github.v3+json',
			'Authorization: token '.$options['token'],
			'User-Agent: Debby/'.$debby_version.' (https://github.com/lode/debby)',
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
 * create github issues for each updatable package
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
 * create github issue for a single package
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
	
	$issue_title = 'Update '.$package->get_manager_name().' package '.$package->get_name();
	$issue_description = template::parse('github_single', $template_data);
	
	$this->create_issue($issue_title, $issue_description);
}

/**
 * create an issue
 * 
 * @param  string $title
 * @param  string $description
 * @return void
 */
private function create_issue($title, $description) {
	$url       = 'https://api.github.com/repos/'.$this->repository.'/issues';
	$arguments = json_encode([
		'title' => $title,
		'body'  => $description,
	]);
	
	$curl_options = [
		CURLOPT_URL        => $url,
		CURLOPT_POST       => true,
		CURLOPT_POSTFIELDS => $arguments,
	];
	curl_setopt_array($this->client, $curl_options);
	
	curl_exec($this->client);
	
	if (curl_errno($this->client)) {
		$e = new exception('github curl error "'.curl_error($this->client).'"');
		$e->stop();
	}
}

}
