<?php

ob_start();

require('loader.php');

define('APP_TITLE', 'Movim');

{
	$index_pos = strpos($_SERVER['PHP_SELF'], 'index.php');
	$path = "";
	if($index_pos <= 0) {
		$path = $_SERVER['PHP_SELF'];
	} else {
		$path = substr($_SERVER['PHP_SELF'], 0, $index_pos);
	}
    // Determining the protocol to use.
    $uri = "http://";
    if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != "") {
        $uri = 'https://';
    }

    if($path == "") {
        $uri .= $_SERVER['HTTP_HOST'] . '/';
    } else {
        $uri .= $_SERVER['HTTP_HOST'] . $path;
    }

	define('BASE_URI', str_replace('jajax.php', '', $uri));
}

?>
