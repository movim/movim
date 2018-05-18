<?php

define('BASE_URI', '(base)');
mb_internal_encoding("UTF-8");

class Configuration
{
    public $theme = '(theme)';

    public static function findOrNew()
    {
        return new Configuration;
    }
}

require_once '../Emoji.php';

$text = file_get_contents('php://stdin');
echo Emoji::getInstance()->replace($text);
