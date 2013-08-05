<?php
function __autoload($className)
{
    $className = ltrim($className, '\\');
    $fileName  = DOCUMENT_ROOT;
    $namespace = '';
    if ($lastNsPos = strrpos($className, '\\')) {
        $namespace = substr($className, 0, $lastNsPos);
        $className = substr($className, $lastNsPos + 1);
        $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    }
    $fileName .= '/'.str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
    if (file_exists($fileName)) {
        require_once( $fileName);
    } else  {
        return false;
    }
}


// A few constants...
define('APP_NAME', 'movim');

define('THEMES_PATH',   DOCUMENT_ROOT . '/themes/');
define('USERS_PATH',    DOCUMENT_ROOT . '/users/');
define('APP_PATH',      DOCUMENT_ROOT . '/app/');
define('SYSTEM_PATH',   DOCUMENT_ROOT . '/system/');
define('LIB_PATH',      DOCUMENT_ROOT . '/lib/');
define('LOCALES_PATH',  DOCUMENT_ROOT . '/locales/');
define('CACHE_PATH',    DOCUMENT_ROOT . '/cache/');

// Loads up all system libraries.
require_once(SYSTEM_PATH . "/i18n/i18n.php");

require_once(SYSTEM_PATH . "Session.php");
require_once(SYSTEM_PATH . "Utils.php");
require_once(SYSTEM_PATH . "UtilsPicture.php");
require_once(SYSTEM_PATH . "Cache.php");
require_once(SYSTEM_PATH . "Conf.php");
require_once(SYSTEM_PATH . "Event.php");
require_once(SYSTEM_PATH . "Logger.php");
require_once(SYSTEM_PATH . "MovimException.php");
require_once(SYSTEM_PATH . "RPC.php");
require_once(SYSTEM_PATH . "User.php");

// LIBRARIES
// XMPPtoForm lib
require_once(LIB_PATH . "XMPPtoForm.php");

// Markdown lib
require_once(LIB_PATH . "Markdown.php");

// We load Movim Data Layer
require_once(LIB_PATH . 'Modl/loader.php');

$db = modl\Modl::getInstance();
$db->setConnectionArray(Conf::getServerConf());

// We load Movim XMPP Library
require_once(LIB_PATH . 'Moxl/loader.php');

require_once(APP_PATH . "controllers/ControllerBase.php");
require_once(APP_PATH . "controllers/ControllerMain.php");
require_once(APP_PATH . "controllers/ControllerAjax.php");

require_once(SYSTEM_PATH . "Route.php");

require_once(SYSTEM_PATH . "Tpl/TplPageBuilder.php");

require_once(SYSTEM_PATH . "widget/WidgetBase.php");
require_once(SYSTEM_PATH . "widget/WidgetWrapper.php");

require_once(APP_PATH . "widgets/WidgetCommon/WidgetCommon.php");
require_once(APP_PATH . "widgets/Notification/Notification.php");

// The template lib
require_once(LIB_PATH . 'RainTPL.php');

// We set the default timezone to the server timezone
$conf = Conf::getServerConf();
date_default_timezone_set($conf['timezone']);

if(isset( $_SERVER['HTTP_USER_AGENT'])) {
    $useragent = $_SERVER['HTTP_USER_AGENT'];

    if (preg_match('|MSIE ([0-9].[0-9]{1,2})|',$useragent,$matched)) {
        $browser_version=$matched[1];
        $browser = 'IE';
    } elseif (preg_match('/Opera[\/ ]([0-9]{1}\.[0-9]{1}([0-9])?)/',$useragent,$matched)) {
        $browser_version=$matched[1];
        $browser = 'Opera';
    } elseif(preg_match('|Firefox/([0-9\.]+)|',$useragent,$matched)) {
        $browser_version=$matched[1];
        $browser = 'Firefox';
    } elseif(preg_match('|Safari/([0-9\.]+)|',$useragent,$matched)) {
        $browser_version=$matched[1];
        $browser = 'Safari';
    }
} else {
    $browser_version = 0;
    $browser= 'other';
}

define('BROWSER_VERSION', $browser_version);
define('BROWSER', $browser);

$compatible = false;

switch($browser) {
    case 'Firefox':
        if($browser_version > 3.5)
            $compatible = true;
    break;
    case 'IE':
        if($browser_version > 9.0)
            $compatible = true;
    break;
    case 'Safari': // Also Chrome-Chromium
        if($browser_version > 522.0)
            $compatible = true;
    break;
    case 'Opera':
        if($browser_version > 9.0)
            $compatible = true;
    break;
}

define('BROWSER_COMP', $compatible);

// Starting session.
$sess = Session::start(APP_NAME);
$session = $sess->get('session');
?>
