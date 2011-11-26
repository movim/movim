<?php

ob_start();

require('loader.php');

define('APP_TITLE', t("MOVIM - Test Client"));

{
	$index_pos = strpos($_SERVER['PHP_SELF'], 'index.php');
	$path = "";
	if($index_pos <= 0) {
		$path = $_SERVER['PHP_SELF'];
	} else {
		$path = substr($_SERVER['PHP_SELF'], 0, $index_pos);
	}
	$uri = "";
	if($path == "") {
		$uri = 'http://' . $_SERVER['HTTP_HOST'] . '/';
	} else {
		$uri = 'http://' . $_SERVER['HTTP_HOST'] . $path;
	}

	define('BASE_URI', $uri);
}

?>
