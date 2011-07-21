<?php

  //ini_set('html_errors', false);

// A few constants...
define('BASE_PATH', dirname(__FILE__) . '/');
define('APP_NAME', 'movim');
define('LIB_PATH', BASE_PATH.'system/');
define('PROPERTIES_PATH', BASE_PATH.'page/properties/');
define('THEMES_PATH', BASE_PATH . 'themes/');

define('SESSION_DB_FILE', BASE_PATH . 'session.db');
define('SESSION_MAX_AGE', 24 * 3600);

// Loads up all system libraries.
require(LIB_PATH . "Lang/i18n.php");

require(LIB_PATH . "Storage/loader.php");
load_storage(array('sqlite'));

require(LIB_PATH . "Session.php");
require(LIB_PATH . "Utils.php");
require(LIB_PATH . "Cache.php");
require(LIB_PATH . "Conf.php");
require(LIB_PATH . "Event.php");
require(LIB_PATH . "Jabber.php");
require(LIB_PATH . "Logger.php");
require(LIB_PATH . "MovimException.php");
require(LIB_PATH . "RPC.php");
require(LIB_PATH . "User.php");

require(LIB_PATH . "Controller/ControllerBase.php");
require(LIB_PATH . "Controller/ControllerMain.php");
require(LIB_PATH . "Controller/ControllerAjax.php");

require(LIB_PATH . "Tpl/TplPageBuilder.php");

require(LIB_PATH . "Widget/WidgetBase.php");
require(LIB_PATH . "Widget/WidgetWrapper.php");


// Starting session.
storage_load_driver(Conf::getServerConfElement('storageDriver'));
StorageEngineWrapper::setdriver(Conf::getServerConfElement('storageDriver'));
Session::start(APP_NAME);

?>
