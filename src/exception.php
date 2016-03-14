<?php

namespace alsvanzelf\debby;

class exception extends \Exception {

/**
 * terminate the cli, explaining why the exception was thrown
 * 
 * @return void
 */
public function stop() {
	if ($this->getCode()) {
		echo '#'.$this->getCode().': ';
	}
	echo $this->getMessage().PHP_EOL;
	
	echo PHP_EOL;
	echo get_class($this).' @ '.$this->getFile().':'.$this->getLine().PHP_EOL;
	echo $this->getTraceAsString().PHP_EOL;
	
	exit(1);
}

/**
 * get a string representation of an E_* error constant
 * 
 * @param  int    $error_severity one of native E_* consts
 * @return string
 */
public static function get_php_native_error_message($error_severity) {
	$possible_errors = [
		E_ERROR             => 'error',
		E_WARNING           => 'warning',
		E_PARSE             => 'parse',
		E_NOTICE            => 'notice',
		E_CORE_ERROR        => 'core error',
		E_CORE_WARNING      => 'core warning',
		E_COMPILE_ERROR     => 'compile error',
		E_COMPILE_WARNING   => 'compile warning',
		E_USER_ERROR        => 'user error',
		E_USER_WARNING      => 'user warning',
		E_USER_NOTICE       => 'user notice',
		E_STRICT            => 'strict',
		E_RECOVERABLE_ERROR => 'recoverable error',
		E_DEPRECATED        => 'deprecated',
		E_USER_DEPRECATED   => 'user deprecated',
		E_ALL               => 'all',
	];
	
	if (array_key_exists($error_severity, $possible_errors) === false) {
		$e = new self('unknown php severity', $error_severity);
		$e->stop();
	}
	
	return $possible_errors[$error_severity];
}

/**
 * get a string representation of a JSON_ERROR_* constant
 * 
 * @param  int    $error_code one of native JSON_ERROR_* consts
 * @return string
 */
public static function get_json_error_message($error_code) {
	$possible_errors = [
		JSON_ERROR_NONE             => 'No error has occurred',
		JSON_ERROR_DEPTH            => 'The maximum stack depth has been exceeded',
		JSON_ERROR_STATE_MISMATCH   => 'Invalid or malformed JSON',
		JSON_ERROR_CTRL_CHAR        => 'Control character error, possibly incorrectly encoded',
		JSON_ERROR_SYNTAX           => 'Syntax error',
		JSON_ERROR_UTF8             => 'Malformed UTF-8 characters, possibly incorrectly encoded',
		JSON_ERROR_RECURSION        => 'One or more recursive references in the value to be encoded',
		JSON_ERROR_INF_OR_NAN       => 'One or more NAN or INF values in the value to be encoded',
		JSON_ERROR_UNSUPPORTED_TYPE => 'A value of a type that cannot be encoded was given',
	];
	
	if (array_key_exists($error_code, $possible_errors) === false) {
		$e = new self('unknown json error', $error_code);
		$e->stop();
	}
	
	return $possible_errors[$error_code];
}

}
