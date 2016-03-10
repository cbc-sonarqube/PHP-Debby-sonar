<?php

namespace alsvanzelf\debby;

class debby {

private $options;

/**
 * make changes to default behavior
 *
 * @param array $options {
 *        @var string $notify_address email address where notification will be sent to
 *                                    required when using ->notify()
 * }
 */
public function __construct(array $options=array()) {
	$this->options = $options;
}

public function check() {
	$results = array();
	
	return $results;
}

public function notify(array $results) {
	if (empty($this->options['notify_address'])) {
		throw new exception('can not notify without email address of the recipient');
	}
	
	$subject = (empty($results)) ? 'All dependencies running fine' : 'Dependency updates needed!';
	$body    = var_export($results, true);
	
	mail($this->options['notify_address'], $subject, $body);
}

}
