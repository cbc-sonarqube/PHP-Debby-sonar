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
	$this->client     = new \Github\Client();
	
	$auth_method = \Github\Client::AUTH_HTTP_TOKEN;
	$this->client->authenticate($options['token'], $password=null, $auth_method);
}

/**
 * notify github with debby results
 * 
 * @param  array $results output from debby->check()
 * 
 * @return void
 */
public function notify(array $results) {
	if (empty($results)) {
		$issue_title       = 'All dependencies running fine';
		$issue_description = debby\template::parse('ticket_fine');
		
		$this->create_issue($issue_title, $issue_description);
		return;
	}
	
	foreach ($results as $package_name => $versions) {
		$this->notify_single_package($package_name, $versions);
	}
}

/**
 * notify github for a single package, creating a ticket
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
	$url       = '/repos/'.$this->repository.'/issues';
	$arguments = json_encode([
		'title' => $title,
		'body'  => $description,
	]);
	
	$this->client->getHttpClient()->post($url, $arguments);
}

}
