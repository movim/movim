<?php

// A few constants...
define('BASE_PATH', dirname(__FILE__) . '/');
define('LIB_PATH',BASE_PATH.'system/');
define('PROPERTIES_PATH',BASE_PATH.'page/properties/');
define('THEMES_PATH', BASE_PATH . 'themes/');

// Loads up all system libraries.
require(LIB_PATH . "Lang/i18n.php");
require(LIB_PATH . "Utils.php");
require(LIB_PATH . "Cache.php");
require(LIB_PATH . "Conf.php");
require(LIB_PATH . "Event.php");
require(LIB_PATH . "Form.php");
require(LIB_PATH . "Jabber.php");
require(LIB_PATH . "MovimException.php");
require(LIB_PATH . "RPC.php");
require(LIB_PATH . "User.php");

require(LIB_PATH . "Controller/ControllerBase.php");
require(LIB_PATH . "Controller/ControllerMain.php");
require(LIB_PATH . "Controller/ControllerAjax.php");

require(LIB_PATH . "Tpl/TplPageBuilder.php");

require(LIB_PATH . "Widget/WidgetBase.php");
require(LIB_PATH . "Widget/WidgetWrapper.php");

require(LIB_PATH . "Storage/StorageDriver.php");
require(LIB_PATH . "Storage/StorageBase.php");
require(LIB_PATH . "Storage/StorageEngineBase.php");
require(LIB_PATH . "Storage/StorageException.php");
require(LIB_PATH . "Storage/StorageTypeBase.php");
require(LIB_PATH . "Storage/StorageType.php");

?>