<?php
if (!defined('DOCUMENT_ROOT')) die('Access denied');

require 'vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\SyslogHandler;

/**
 * Error Handler...
 */
function systemErrorHandler($errno, $errstr, $errfile, $errline, $errcontext = null) 
{
    $log = new Logger('movim');
    $log->pushHandler(new SyslogHandler('movim'));
    $log->addError($errstr);
    return false;
}

/**
 * Manage boot order
 */
class Bootstrap {
    function boot($light = false) {
        //define all needed constants
        $this->setConstants();
        
        mb_internal_encoding("UTF-8");

        //First thing to do, define error management (in case of error forward)
        $this->setLogs();
        
        //Check if vital system need is OK
        $this->checkSystem();

        if(!$light) $this->setBrowserSupport();
        
        $this->loadSystem();
        $this->loadCommonLibraries();
        $this->loadDispatcher();
        $this->loadHelpers();
        
        $loadmodlsuccess = $this->loadModl();

        $this->setTimezone();
        $this->setLogLevel();

        if($loadmodlsuccess) {
            $this->startingSession();
            $this->loadLanguage();
        } else {
            throw new Exception('Error loading Modl');
        }
    }
    private function checkSystem() {
        $listWritableFile = array(
            DOCUMENT_ROOT.'/log/logger.log',
            DOCUMENT_ROOT.'/log/php.log',
            DOCUMENT_ROOT.'/cache/test.tmp',
        );
        $errors=array();
        
        if(!is_writable(DOCUMENT_ROOT))
            $errors[] = 'We\'re unable to write to folder '.DOCUMENT_ROOT.': check rights';
        else {
            if(!file_exists(DOCUMENT_ROOT.'/cache') && !@mkdir(DOCUMENT_ROOT.'/cache')) {
                $errors[] = 'Couldn\'t create directory cache';
            }
            if(!file_exists(DOCUMENT_ROOT.'/log') && !@mkdir(DOCUMENT_ROOT.'/log')) {
                $errors[] = 'Couldn\'t create directory log';
            }
            if(!file_exists(DOCUMENT_ROOT.'/config') && !@mkdir(DOCUMENT_ROOT.'/config')) {
                $errors[] = 'Couldn\'t create directory config';
            }
            if(!file_exists(DOCUMENT_ROOT.'/users') && !@mkdir(DOCUMENT_ROOT.'/users')) {
                $errors[] = 'Couldn\'t create directory users';
            } else {
                touch(DOCUMENT_ROOT.'/users/index.html');
            }
        }
        
        foreach($listWritableFile as $fileName) {
            if (!file_exists($fileName)) {
                if (touch($fileName) !== true) {
                    $errors[] = 'We\'re unable to write to '.$fileName.': check rights';
                } 
            }else if (is_writable($fileName) !== true) {
                $errors[] = 'We\'re unable to write to file '.$fileName.': check rights';
            }
        }
        if (!function_exists('json_decode')) {
             $errors[] = 'You need to install php5-json that\'s not seems to be installed';
        }
        if (count($errors)) {
            throw new Exception(implode("\n<br />",$errors));
        }
    }
    private function setConstants() {
        define('APP_TITLE',     'Movim');
        define('APP_NAME',      'movim');
        define('APP_VERSION',   $this->getVersion());
        define('APP_SECURED',   $this->isServerSecured());

        if(isset($_SERVER['HTTP_HOST'])) {
            define('BASE_HOST',     $_SERVER['HTTP_HOST']);
        }

        if(isset($_SERVER['SERVER_NAME'])) {
            define('BASE_DOMAIN',   $_SERVER["SERVER_NAME"]);
        }

        define('BASE_URI',      $this->getBaseUri());
        define('CACHE_URI',     $this->getBaseUri() . 'cache/');
        
        define('SESSION_ID',    getenv('sid'));
        
        define('THEMES_PATH',   DOCUMENT_ROOT . '/themes/');
        define('USERS_PATH',    DOCUMENT_ROOT . '/users/');
        define('APP_PATH',      DOCUMENT_ROOT . '/app/');
        define('SYSTEM_PATH',   DOCUMENT_ROOT . '/system/');
        define('LIB_PATH',      DOCUMENT_ROOT . '/lib/');
        define('LOCALES_PATH',  DOCUMENT_ROOT . '/locales/');
        define('CACHE_PATH',    DOCUMENT_ROOT . '/cache/');
        define('LOG_PATH',      DOCUMENT_ROOT . '/log/');
        
        define('VIEWS_PATH',    DOCUMENT_ROOT . '/app/views/');
        define('HELPERS_PATH',  DOCUMENT_ROOT . '/app/helpers/');
        define('WIDGETS_PATH',  DOCUMENT_ROOT . '/app/widgets/');
        
        define('MOVIM_API',     'https://api.movim.eu/');
        
        if (!defined('DOCTYPE')) {
            define('DOCTYPE','text/html');
        }
    }

    private function isServerSecured() {
        if((
            isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != "") 
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == "https")) {
            return true;
        }

        return false;
    }

    private function getVersion() {
        $file = "VERSION";
        if($f = fopen(DOCUMENT_ROOT.'/'.$file, 'r')) {
            return trim(fgets($f));
        }
    }
    
    private function getBaseUri() {
        $dirname = dirname($_SERVER['PHP_SELF']);
        $path = (($dirname == DIRECTORY_SEPARATOR) ? '' : $dirname).'/';

        // Determining the protocol to use.
        $uri = "http://";
        if($this->isServerSecured()) {
            $uri = 'https://';
        }

        if($path == "") {
            $uri .= $_SERVER['HTTP_HOST'] ;
        } elseif(isset($_SERVER['HTTP_HOST'])) {
            $uri .= str_replace('//', '/', $_SERVER['HTTP_HOST'] . $path);
        }

        if(getenv('baseuri') != null
        && filter_var(getenv('baseuri'), FILTER_VALIDATE_URL)
        && sizeof(getenv('baseuri')) < 32) {
            return getenv('baseuri');
        } else {
            return $uri;
        }
    }
    
    private function loadSystem() {
        // Loads up all system libraries.
        require_once(SYSTEM_PATH . "/i18n/i18n.php");

        require_once(SYSTEM_PATH . "Session.php");
        require_once(SYSTEM_PATH . "Sessionx.php");
        require_once(SYSTEM_PATH . "Utils.php");
        require_once(SYSTEM_PATH . "UtilsPicture.php");
        require_once(SYSTEM_PATH . "Cache.php");
        require_once(SYSTEM_PATH . "Event.php");
        require_once(SYSTEM_PATH . "MovimException.php");
        require_once(SYSTEM_PATH . "RPC.php");
        require_once(SYSTEM_PATH . "User.php");
        require_once(SYSTEM_PATH . "Picture.php");
    }
    
    private function loadCommonLibraries() {
        // XMPPtoForm lib
        require_once(LIB_PATH . "XMPPtoForm.php");
        
        // SDPtoJingle and JingletoSDP lib :)
        require_once(LIB_PATH . "SDPtoJingle.php");
        require_once(LIB_PATH . "JingletoSDP.php");
    }

    private function loadHelpers() {
        foreach(glob(HELPERS_PATH."*Helper.php") as $file) {
            require $file;
        }
    }
    
    private function loadDispatcher() {
        require_once(SYSTEM_PATH . "template/TplPageBuilder.php");
        require_once(SYSTEM_PATH . "controllers/BaseController.php");
        require_once(SYSTEM_PATH . "controllers/AjaxController.php");

        require_once(SYSTEM_PATH . "Route.php");

        require_once(SYSTEM_PATH . "controllers/FrontController.php");

        require_once(SYSTEM_PATH . "widget/WidgetBase.php");
        require_once(SYSTEM_PATH . "widget/WidgetWrapper.php");

        require_once(APP_PATH . "widgets/WidgetCommon/WidgetCommon.php");
        require_once(APP_PATH . "widgets/Notification/Notification.php");
    }

    /**
     * Loads up the language, either from the User or default.
     */
    function loadLanguage() {
        $user = new User();
        $user->reload();

        $cd = new \Modl\ConfigDAO();
        $config = $cd->get();
        
        if($user->isLogged()) {
            $lang = $user->getConfig('language');
            if(isset($lang)) {
                loadLanguage($lang);
            } else {
                // Load default language.
                loadLanguage($config->locale);
            }
        }
        else if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            loadLanguageAuto();
        }
        else {
            loadLanguage($config->locale);
        }
    }
    
    private function setLogs() {
        /*$cd = new \Modl\ConfigDAO();
        $config = $cd->get();
        
        try {
            define('ENVIRONMENT', $config->environment);
        } catch (Exception $e) {
            define('ENVIRONMENT','development');//default environment is production
        }*/
        define('ENVIRONMENT','development');//default environment is production
        /**
         * LOG_MANAGEMENT: define where logs are saved, prefer error_log, or log_folder if you use mutual server.
         * 'error_log'  : save in file defined on your file server
         * 'log_folder' : save in log folder, in DOCUMENT_ROOT.'/log'
         * 'syslog'     : save in global system logs (not in file server logs)
         */
         
        define('LOG_MANAGEMENT','log_folder');
        if (ENVIRONMENT === 'development') {
            ini_set('log_errors', 1);
            ini_set('display_errors', 0);
            ini_set('error_reporting', E_ALL );
        
        } else {
            ini_set('log_errors', 1);
            ini_set('display_errors', 0);
            ini_set('error_reporting', E_ALL ^ E_DEPRECATED ^ E_NOTICE);
        }
        if (LOG_MANAGEMENT === 'log_folder') {
            ini_set('error_log', DOCUMENT_ROOT.'/log/php.log');
        }
        set_error_handler('systemErrorHandler', E_ALL);
    }
    
    private function setTimezone() {
        // We set the default timezone to the server timezone
        $cd = new \Modl\ConfigDAO();
        $config = $cd->get();

        // And we set a global offset
        define('TIMEZONE_OFFSET', getTimezoneOffset($config->timezone));
        
        date_default_timezone_set($config->timezone);
    }

    private function setLogLevel() {
        // We set the default timezone to the server timezone
        $cd = new \Modl\ConfigDAO();
        $config = $cd->get();

        define('LOG_LEVEL', (int)$config->loglevel);
    }

    private function loadModl() {
        // We load Movim Data Layer
        $db = Modl\Modl::getInstance();
        $db->setModelsPath(APP_PATH.'models');
        
        Modl\Utils::loadModel('Config');
        Modl\Utils::loadModel('Presence');
        Modl\Utils::loadModel('Contact');
        Modl\Utils::loadModel('Privacy');
        Modl\Utils::loadModel('RosterLink');
        Modl\Utils::loadModel('Cache');
        Modl\Utils::loadModel('Postn');
        Modl\Utils::loadModel('Subscription');
        Modl\Utils::loadModel('Caps');
        Modl\Utils::loadModel('Item');
        Modl\Utils::loadModel('Message');
        Modl\Utils::loadModel('Sessionx');
        Modl\Utils::loadModel('Conference');

        if(file_exists(DOCUMENT_ROOT.'/config/db.inc.php')) {
            require DOCUMENT_ROOT.'/config/db.inc.php';
        } else {
            throw new MovimException('Cannot find config/db.inc.php file');
        }
        
        $db->setConnectionArray($conf);
        $db->connect();

        return true;
    }
    
    private function setBrowserSupport() {
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
            } else {
                $browser_version = 0;
                $browser = 'other';
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
                if($browser_version > 10.0)
                    $compatible = true;
            break;
            case 'Safari': // Also Chrome-Chromium
                if($browser_version > 522.0)
                    $compatible = true;
            break;
            case 'Opera':
                if($browser_version > 12.1)
                    $compatible = true;
            break;
        }

        define('BROWSER_COMP', $compatible);
    }
    
    private function startingSession() {
        $s = \Sessionx::start();
        $s->load();

        $user = new User;
        $db = modl\Modl::getInstance();
        $db->setUser($user->getLogin());
    }
}
