<?php

namespace alsvanzelf\debby;

class template {

/**
 * load and fill a template with key-value pairs from $data
 * 
 * templates use mustache alike replacements: {{key}}
 * 
 * @param  string $name file name of the template from src/templates/*.txt
 * @param  array  $data optional
 * @return string       template contents with keys replaced
 */
public static function parse($name, array $data=[]) {
	$template = file_get_contents(__DIR__.'/templates/'.$name.'.txt');
	
	foreach ($data as $key => $value) {
		$template = str_replace('{{'.$key.'}}', $value, $template);
	}
	
	return $template;
}

}
