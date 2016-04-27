<?php

namespace alsvanzelf\debby\channel;

use alsvanzelf\debby;
use alsvanzelf\debby\exception;
use alsvanzelf\debby\template;

class email implements channel {

private $client;
private $recipient;

/**
 * setup a smtp connection
 * 
 * @param array $options {
 *              @var $recipient
 *              @var $host
 *              @var $port
 *              @var $security
 *              @var $user
 *              @var $pass
 * }
 */
public function __construct(array $options=[]) {
	if (class_exists('\Swift_Mailer') === false) {
		$e = new exception('can not notify via email without swiftmailer');
		$e->stop();
	}
	
	$transport = new \Swift_SmtpTransport($options['host'], $options['port'], $options['security']);
	$transport->setUsername($options['user']);
	$transport->setPassword($options['pass']);
	
	$this->recipient = $options['recipient'];
	$this->client = new \Swift_Mailer($transport);
}

/**
 * send an email with all updatable packages
 * 
 * @param  array<package> $packages as returned by debby->check()
 * @return void
 */
public function send(array $packages) {
	if (debby\VERBOSE) {
		debby\debby::log('Notifying email');
	}
	
	$package_lines = '';
	foreach ($packages as $package) {
		$template_data = [
			'name'      => $package->get_name(),
			'manager'   => $package->get_manager_name(),
			'required'  => $package->get_required_version(),
			'installed' => $package->get_installed_version(),
			'latest'    => $package->get_latest_version(),
		];
		
		$package_lines .= template::parse('email_multiple_line', $template_data);
	}
	
	$subject = 'Dependency updates needed!';
	$body    = template::parse('email_multiple', ['packages' => $package_lines]);
	
	$this->send_email($subject, $body);
	
	if (debby\VERBOSE) {
		debby\debby::log("\t".'Done');
	}
}

/**
 * send an email
 * 
 * @param  string $subject
 * @param  string $body
 * @return void
 */
private function send_email($subject, $body) {
	$message = new \Swift_Message();
	$message->setFrom($this->recipient);
	$message->setTo($this->recipient);
	$message->setSubject($subject);
	$message->setBody($body);
	
	$this->client->send($message);
}

}
