<?php

define('BASE_URI', '(base)');
mb_internal_encoding("UTF-8");

class Configuration {
	public $theme = '(theme)';

	public static function findOrNew() {
		return new Configuration;
	}
}

require_once 'MovimEmoji.php';

$text = file_get_contents('php://stdin');
echo MovimEmoji::getInstance()->replace($text);
