<?php

namespace alsvanzelf\debby;

class template {

public static function parse($name, array $data=[]) {
	$template = file_get_contents(__DIR__.'/templates/'.$name.'.txt');
	
	foreach ($data as $key => $value) {
		$template = str_replace('{{'.$key.'}}', $value, $template);
	}
	
	return $template;
}

}
