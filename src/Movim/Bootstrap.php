<?php
namespace Movim;

use Monolog\Logger;
use Monolog\Handler\SyslogHandler;

class Bootstrap
{
    function boot()
    {
        //define all needed constants
        $this->setConstants();

        mb_internal_encoding("UTF-8");

        //First thing to do, define error management (in case of error forward)
        $this->setLogs();

        //Check if vital system need is OK
        $this->checkSystem();

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
            throw new \Exception('Error loading Modl');
        }
    }

    private function checkSystem()
    {
        $listWritableFile = array(
            DOCUMENT_ROOT.'/log/logger.log',
            DOCUMENT_ROOT.'/log/php.log',
            DOCUMENT_ROOT.'/cache/test.tmp',
        );
        $errors = [];

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
            throw new \Exception(implode("\n<br />",$errors));
        }
    }

    private function setConstants()
    {
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

    private function isServerSecured()
    {
        if((
            isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != "")
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == "https")) {
            return true;
        }

        return false;
    }

    private function getVersion()
    {
        $file = "VERSION";
        if($f = fopen(DOCUMENT_ROOT.'/'.$file, 'r')) {
            return trim(fgets($f));
        }
    }

    private function getBaseUri()
    {
        $dirname = dirname($_SERVER['PHP_SELF']);

        if(strstr($dirname, 'index.php')) {
            $dirname = substr($dirname, 0, strrpos($dirname, 'index.php'));
        }

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

    private function loadSystem()
    {
        require_once(SYSTEM_PATH . "Session.php");
        require_once(SYSTEM_PATH . "Sessionx.php");
        require_once(SYSTEM_PATH . "Cache.php");
        require_once(SYSTEM_PATH . "Event.php");
        require_once(SYSTEM_PATH . "RPC.php");
        require_once(SYSTEM_PATH . "User.php");
        require_once(SYSTEM_PATH . "Picture.php");
    }

    private function loadCommonLibraries()
    {
        // XMPPtoForm lib
        require_once(LIB_PATH . "XMPPtoForm.php");

        // SDPtoJingle and JingletoSDP lib :)
        //require_once(LIB_PATH . "SDPtoJingle.php");
        //require_once(LIB_PATH . "JingletoSDP.php");
    }

    private function loadHelpers()
    {
        foreach(glob(HELPERS_PATH."*Helper.php") as $file) {
            require $file;
        }
    }

    private function loadDispatcher()
    {
        require_once(SYSTEM_PATH . "Route.php");
        require_once(APP_PATH . "widgets/Notification/Notification.php");
    }

    /**
     * Loads up the language, either from the User or default.
     */
    function loadLanguage()
    {
        $user = new \User();
        $user->reload();

        $cd = new \Modl\ConfigDAO();
        $config = $cd->get();

        $l = \Movim\i18n\Locale::start();

        if($user->isLogged()) {
            $lang = $user->getConfig('language');
        }

        if(isset($lang)) {
            $l->load($lang);
        } elseif(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $l->detect();
            $l->loadPo();
        } else {
            $l->load($config->locale);
        }
    }

    private function setLogs()
    {
        ini_set('display_errors', 0);
        ini_set('error_log', DOCUMENT_ROOT.'/log/php.log');

        set_error_handler([$this, 'systemErrorHandler'], E_ALL);
        register_shutdown_function([$this, 'fatalErrorShutdownHandler']);
    }

    private function setTimezone()
    {
        // We set the default timezone to the server timezone
        $cd = new \Modl\ConfigDAO();
        $config = $cd->get();

        // And we set a global offset
        define('TIMEZONE_OFFSET', getTimezoneOffset($config->timezone));

        date_default_timezone_set($config->timezone);
    }

    private function setLogLevel()
    {
        // We set the default timezone to the server timezone
        $cd = new \Modl\ConfigDAO();
        $config = $cd->get();

        define('LOG_LEVEL', (int)$config->loglevel);
    }

    private function loadModl()
    {
        // We load Movim Data Layer
        $db = \Modl\Modl::getInstance();
        $db->setModelsPath(APP_PATH.'models');

        \Modl\Utils::loadModel('Config');
        \Modl\Utils::loadModel('Presence');
        \Modl\Utils::loadModel('Contact');
        \Modl\Utils::loadModel('Privacy');
        \Modl\Utils::loadModel('RosterLink');
        \Modl\Utils::loadModel('Cache');
        \Modl\Utils::loadModel('Postn');
        \Modl\Utils::loadModel('Subscription');
        \Modl\Utils::loadModel('Caps');
        \Modl\Utils::loadModel('Item');
        \Modl\Utils::loadModel('Message');
        \Modl\Utils::loadModel('Sessionx');
        \Modl\Utils::loadModel('Conference');
        \Modl\Utils::loadModel('Tag');

        if(file_exists(DOCUMENT_ROOT.'/config/db.inc.php')) {
            require DOCUMENT_ROOT.'/config/db.inc.php';
        } else {
            throw new \Exception('Cannot find config/db.inc.php file');
        }

        $db->setConnectionArray($conf);
        $db->connect();

        return true;
    }

    private function startingSession()
    {
        $s = \Sessionx::start();
        $s->load();

        $user = new \User;
        $db = \Modl\Modl::getInstance();
        $db->setUser($user->getLogin());
    }

    public function getWidgets()
    {
        // Return a list of interesting widgets to load (to save memory)
        return["Account","AccountNext","Ack","AdHoc","Avatar","Bookmark","Chat",
        "Chats","Config","Contact","Dialog","Drawer","Group","Groups","Header",
        "Init","Login","LoginAnonymous","Menu","Notifs","Post","Presence",
        "Publish","Rooms","Roster","Stickers","Upload","Vcard4"];
    }

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

    function fatalErrorShutdownHandler()
    {
        $last_error = error_get_last();
        if($last_error['type'] === E_ERROR) {
            $this->systemErrorHandler(
                E_ERROR,
                $last_error['message'],
                $last_error['file'],
                $last_error['line']);

            if (ob_get_contents()) ob_clean();

            echo "Oops... something went wrong.\n";
            echo "But don't panic. The NSA is on the case.\n";

            if (ob_get_contents()) ob_end_clean();
        }
    }
}
