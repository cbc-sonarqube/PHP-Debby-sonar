<?php

namespace alsvanzelf\debby\notify;

use alsvanzelf\debby;

class github {

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
public function __construct(array $options) {
	if (empty($options['token']) || empty($options['repository'])) {
		throw new debby\exception('github notifications require a token and a repository option');
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
 * notify github with debby results
 * 
 * @param  array $results output from debby->check()
 * 
 * @return void
 */
public function notify(array $results) {
	foreach ($results as $package_name => $versions) {
		$this->notify_single_package($package_name, $versions);
	}
}

/**
 * notify github for a single package, creating an issue
 * 
 * @param  string $package_name
 * @param  array  $versions     {
 *         @var $required
 *         @var $installed
 *         @var $possible
 * }
 * 
 * @return void
 */
private function notify_single_package($package_name, array $versions) {
	$template_data = ['name' => $package_name] + $versions;
	
	$issue_title = 'Update composer package '.$package_name;
	$issue_description = debby\template::parse('ticket_package', $template_data);
	
	$this->create_issue($issue_title, $issue_description);
}

/**
 * create an issue
 * 
 * @param  string $title
 * @param  string $description
 * 
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
		throw new debby\exception('github curl error "'.curl_error($this->client).'"');
	}
}

}
