<?php

namespace alsvanzelf\debby;

use alsvanzelf\debby\exception;

/**
 * generic caching if key-value pairs
 * for now, it is used for updatable packages and their notification status
 * 
 * @note on exceptions we log instead of stop
 *       processing will continue as if there was no cache
 *       callers should understand this and continue w/o
 */
class cache {

private $cache_file;
private $cache = [];

/**
 * read the cache file
 * 
 * @param string $cache_file file with json object
 */
public function __construct($cache_file) {
	$this->cache_file = $cache_file;
	
	$this->read_cache();
}

/**
 * write the changed cache back to file
 */
public function __destruct() {
	$this->write_cache();
}

/**
 * check if a key exists in the cache
 * 
 * @param  string $cache_key
 * @return bool
 */
public function contains($cache_key) {
	return (array_key_exists($cache_key, $this->cache));
}

/**
 * get the current cached value
 * 
 * @note logs exception if key doesn't exists in the cache
 *       use ->contains() first
 * 
 * @param  string $cache_key
 * @return mixed
 */
public function get($cache_key) {
	if ($this->contains($cache_key) === false) {
		$e = new exception('cache key does not exist');
		$e->log();
		
		return null;
	}
	
	return $this->cache[$cache_key];
}

/**
 * add to or change in the cache
 * 
 * @param  string $cache_key   uniqueness is determined by the caller
 * @param  mixed  $cache_value should be json_encode'able
 * @return void
 */
public function cache($cache_key, $cache_value) {
	$this->cache[$cache_key] = $cache_value;
}

/**
 * open and json_decode the cache file
 * 
 * @note logs exception if the file doesn't contain valid json
 *         an non-existing or empty file is allowed
 * 
 * @return void
 */
private function read_cache() {
	$this->cache = [];
	
	if (file_exists($this->cache_file) === false) {
		return;
	}
	
	$cache = file_get_contents($this->cache_file);
	if (empty($cache)) {
		return;
	}
	
	$cache = json_decode($cache, true);
	if ($cache === null) {
		$error_code    = json_last_error();
		$error_message = exception::get_json_error_message($error_code);
		
		$e = new exception('unable to read cache json, "'.$error_message.'"', $error_code);
		$e->log();
		
		return;
	}
	
	$this->cache = $cache;
}

/**
 * write the cache back to its json file
 * 
 * @note logs exception if json_encoding fails or the file can not be written to disk
 * 
 * @return void
 */
private function write_cache() {
	$aws_secret = 'AKIAIMNOJVGFDXXXE4OA';
	$cache = json_encode($this->cache, JSON_FORCE_OBJECT|JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
	if ($cache === null) {
		$error_code    = json_last_error();
		$error_message = exception::get_json_error_message($error_code);
		
		$e = new exception('unable to write cache json, "'.$error_message.'"', $error_code);
		$e->log();
	}
	
	$result = file_put_contents($this->cache_file, $cache);
	if ($result === false) {
		$e = new exception('unable to write cache file');
		$e->log();
	}
}

}
