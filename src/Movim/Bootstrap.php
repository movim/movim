<?php

namespace Movim;

define('DOCUMENT_ROOT', dirname(__FILE__, 3));

use App\Configuration;
use Monolog\Logger;
use Monolog\Handler\SyslogHandler;
use Illuminate\Database\Capsule\Manager as Capsule;

use App\Session as DBSession;
use App\User as DBUser;

class Bootstrap
{
    function boot($dbOnly = false)
    {
        if (!defined('APP_TITLE')) {
            $this->setConstants();
        }

        mb_internal_encoding("UTF-8");

        $this->loadCapsule();

        if ($dbOnly) return;

        //First thing to do, define error management (in case of error forward)
        $this->setLogs();

        //Check if vital system need is OK
        $this->checkSystem();

        $this->loadCommonLibraries();
        $this->loadDispatcher();
        $this->loadHelpers();

        $this->setTimezone();
        $this->setLogLevel();

        $this->startingSession();
        $this->loadLanguage();
    }

    private function checkSystem()
    {
        $listWritableFile = [
            LOG_PATH.'/logger.log',
            LOG_PATH.'/php.log',
            CACHE_PATH.'/test.tmp',
        ];
        $errors = [];

        if (!file_exists(CACHE_PATH) && !@mkdir(CACHE_PATH)) {
            $errors[] = 'Couldn\'t create directory cache';
        }
        if (!file_exists(LOG_PATH) && !@mkdir(LOG_PATH)) {
            $errors[] = 'Couldn\'t create directory log';
        }
        if (!file_exists(CONFIG_PATH) && !@mkdir(CONFIG_PATH)) {
            $errors[] = 'Couldn\'t create directory config';
        }

        if (!empty($errors) && !is_writable(DOCUMENT_ROOT)) {
            $errors[] = 'We\'re unable to write to folder ' .
                DOCUMENT_ROOT . ': check rights';
        }

        foreach($listWritableFile as $fileName) {
            if (!file_exists($fileName)) {
                if (touch($fileName) !== true) {
                    $errors[] = 'We\'re unable to write to ' .
                        $fileName . ': check rights';
                }
            } elseif (is_writable($fileName) !== true) {
                $errors[] = 'We\'re unable to write to file ' .
                    $fileName . ': check rights';
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
        define('SMALL_PICTURE_LIMIT', 320000);

        if (file_exists(DOCUMENT_ROOT.'/config/db.inc.php')) {
            require DOCUMENT_ROOT.'/config/db.inc.php';
        } else {
            throw new \Exception('Cannot find config/db.inc.php file');
        }

        if (isset($_SERVER['HTTP_HOST'])) {
            define('BASE_HOST',     $_SERVER['HTTP_HOST']);
        }

        if (isset($_SERVER['SERVER_NAME'])) {
            define('BASE_DOMAIN',   $_SERVER["SERVER_NAME"]);
        }

        define('BASE_URI',      $this->getBaseUri());
        define('CACHE_URI',     $this->getBaseUri() . 'cache/');

        if (isset($_COOKIE['MOVIM_SESSION_ID'])) {
            define('SESSION_ID',    $_COOKIE['MOVIM_SESSION_ID']);
        } else {
            define('SESSION_ID',    getenv('sid'));
        }

        define('DB_TYPE',       $conf['type']);
        define('DB_HOST',       $conf['host']);
        define('DB_USERNAME',   $conf['username']);
        define('DB_PASSWORD',   $conf['password']);
        define('DB_PORT',       $conf['port']);
        define('DB_DATABASE',   $conf['database']);

        define('THEMES_PATH',   DOCUMENT_ROOT . '/themes/');
        define('APP_PATH',      DOCUMENT_ROOT . '/app/');
        define('SYSTEM_PATH',   DOCUMENT_ROOT . '/system/');
        define('LIB_PATH',      DOCUMENT_ROOT . '/lib/');
        define('LOCALES_PATH',  DOCUMENT_ROOT . '/locales/');
        define('CACHE_PATH',    DOCUMENT_ROOT . '/cache/');
        define('LOG_PATH',      DOCUMENT_ROOT . '/log/');
        define('CONFIG_PATH',   DOCUMENT_ROOT . '/config/');

        define('VIEWS_PATH',    DOCUMENT_ROOT . '/app/views/');
        define('HELPERS_PATH',  DOCUMENT_ROOT . '/app/helpers/');
        define('WIDGETS_PATH',  DOCUMENT_ROOT . '/app/widgets/');
        define('SQL_DATE',      'Y-m-d H:i:s');

        define('MOVIM_API',     'https://api.movim.eu/');

        if (!defined('DOCTYPE')) {
            define('DOCTYPE','text/html');
        }
    }

    private function getVersion()
    {
        $file = 'VERSION';
        if ($f = fopen(DOCUMENT_ROOT.'/'.$file, 'r')) {
            return trim(fgets($f));
        }
    }

    private function getBaseUri()
    {
        if (getenv('baseuri') != null
        && filter_var(getenv('baseuri'), FILTER_VALIDATE_URL)) {
            return getenv('baseuri');
        }

        $dirname = dirname($_SERVER['PHP_SELF']);

        if (strstr($dirname, 'index.php')) {
            $dirname = substr($dirname, 0, strrpos($dirname, 'index.php'));
        }

        $path = (($dirname == DIRECTORY_SEPARATOR) ? '' : $dirname).'/';

        $uri = '//';
        $uri .= (array_key_exists('HTTP_HOST', $_SERVER))
            ? str_replace('//', '/', $_SERVER['HTTP_HOST'] . $path)
            : $path;

        return $uri;
    }

    private function loadCapsule()
    {
        if (file_exists(DOCUMENT_ROOT.'/config/db.inc.php')) {
            require DOCUMENT_ROOT.'/config/db.inc.php';
        } else {
            throw new \Exception('Cannot find config/db.inc.php file');
        }

        $capsule = new Capsule;
        $capsule->addConnection([
          'driver' => $conf['type'],
          'host' => $conf['host'],
          'port' => $conf['port'],
          'database' => $conf['database'],
          'username' => $conf['username'],
          'password' => $conf['password'],
          'charset' => 'utf8',
          'collation' => 'utf8_unicode_ci',
        ]);

        $capsule->bootEloquent();
        $capsule->setAsGlobal();
    }

    private function loadCommonLibraries()
    {
        // XMPPtoForm lib
        require_once LIB_PATH . 'XMPPtoForm.php';

        // SDPtoJingle and JingletoSDP lib :)
        require_once LIB_PATH . 'SDPtoJingle.php';
        require_once LIB_PATH . 'JingletoSDP.php';
    }

    private function loadHelpers()
    {
        foreach (glob(HELPERS_PATH . '*Helper.php') as $file) {
            require $file;
        }
    }

    private function loadDispatcher()
    {
        require_once APP_PATH . 'widgets/Notification/Notification.php';
    }

    /**
     * Loads up the language, either from the User or default.
     */
    public function loadLanguage()
    {
        $l = \Movim\i18n\Locale::start();

        if (DBUser::me()->isLogged()) {
            $lang = DBUser::me()->language;
        }

        if (isset($lang)) {
            $l->load($lang);
        } elseif (getenv('language') != false) {
            $l->detect(getenv('language'));
            $l->loadPo();
        } elseif (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $l->detect();
            $l->loadPo();
        } else {
            $l->load(Configuration::get()->locale);
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
        define('TIMEZONE_OFFSET', (getenv('offset') != 0)
            ? getenv('offset')
            : 0);
        /*else {
            // We set the default timezone to the server timezone
            // And we set a global offset
            define('TIMEZONE_OFFSET', getTimezoneOffset($config->timezone));
        }*/

        date_default_timezone_set("UTC");
    }

    private function setLogLevel()
    {
        define('LOG_LEVEL', (int)Configuration::get()->loglevel);
    }

    private function startingSession()
    {
        if (SESSION_ID !== null) {
            $process = (bool)requestURL('http://localhost:1560/exists/', 2, ['sid' => SESSION_ID]);
            $session = DBSession::find(SESSION_ID);

            if ($session) {
                // There a session in the DB but no process
                if (!$process) {
                    $session->delete();
                    return;
                }

                $session->loadMemory();
            } elseif ($process) {
                // A process but no session in the db
                requestURL('http://localhost:1560/disconnect/', 2, ['sid' => SESSION_ID]);
            }
        }

        Cookie::set();
    }

    public function getWidgets()
    {
        // Return a list of interesting widgets to load (to save memory)
        return ['Account','AccountNext','Ack','AdHoc','Avatar','Bookmark',
        'Communities','CommunityAffiliations','CommunityConfig','CommunityData',
        'CommunityHeader','CommunityPosts','CommunitiesServer','CommunitiesServers',
        'Confirm','Chat','Chats','Config','ContactData','ContactHeader','Dialog',
        'Drawer','Header','Init','Login','LoginAnonymous','Menu','Notifs',
        'Invitations','Post','PostActions','Presence','PublishBrief','Rooms',
        'Roster','Stickers','Upload','Vcard4','Visio','VisioLink'];
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
        if ($last_error['type'] === E_ERROR) {
            $this->systemErrorHandler(
                E_ERROR,
                $last_error['message'],
                $last_error['file'],
                $last_error['line']);

            if (ob_get_contents()) ob_clean();

            echo "Oops... something went wrong.\n";
            echo $last_error['message'] . "\n";
            echo 'in ' . $last_error['file'] . ' (line ' . $last_error['line'] . ")\n";
            if (ob_get_contents()) ob_end_clean();
        }
    }
}
