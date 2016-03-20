<?php

namespace alsvanzelf\debby\channel;

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
	$package_lines = '';
	foreach ($packages as $package_name => $versions) {
		$template_data = ['name' => $package_name] + $versions;
		$package_lines .= template::parse('email_package', $template_data);
	}
	
	$subject = 'Dependency updates needed!';
	$body    = template::parse('email_updates', ['packages' => $package_lines]);
	
	$this->send_email($subject, $body);
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
