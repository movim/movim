<?php

namespace Movim;

define('DOCUMENT_ROOT', dirname(__FILE__, 3));

use App\Configuration;
use Illuminate\Database\Capsule\Manager as Capsule;

use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

use App\Session as DBSession;
use App\User as DBUser;

class Bootstrap
{
    public function boot($dbOnly = false)
    {
        $this->setLogs();

        if (!defined('APP_TITLE')) {
            $this->setConstants();
        }

        mb_internal_encoding('UTF-8');

        $this->loadCapsule();

        if ($dbOnly) {
            return;
        }

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
        if (!file_exists(CACHE_PATH) && !@mkdir(CACHE_PATH)) {
            throw new \Exception('Couldn’t create cache directory');
        }

        if (!file_exists(PUBLIC_CACHE_PATH) && !@mkdir(PUBLIC_CACHE_PATH)) {
            throw new \Exception('Couldn’t create public cache directory');
        }

        if (!file_exists(LOG_PATH) && !@mkdir(LOG_PATH)) {
            throw new \Exception('Couldn’t create log directory');
        }
    }

    private function setConstants()
    {
        define('APP_TITLE', 'Movim');
        define('APP_NAME', 'movim');
        define('APP_VERSION', $this->getVersion());
        define('SMALL_PICTURE_LIMIT', 768000);

        if (file_exists(DOCUMENT_ROOT.'/config/db.inc.php')) {
            require DOCUMENT_ROOT.'/config/db.inc.php';
        } else {
            throw new \Exception('Cannot find config/db.inc.php file');
        }

        if (isset($_SERVER['HTTP_HOST'])) {
            define('BASE_HOST', $_SERVER['HTTP_HOST']);
        }

        if (isset($_SERVER['SERVER_NAME'])) {
            define('BASE_DOMAIN', $_SERVER["SERVER_NAME"]);
        }

        define('BASE_URI', $this->getBaseUri());

        if (isset($_COOKIE['MOVIM_SESSION_ID'])) {
            define('SESSION_ID', $_COOKIE['MOVIM_SESSION_ID']);
        } else {
            define('SESSION_ID', getenv('sid'));
        }

        define('DB_TYPE', $conf['type']);
        define('DB_HOST', $conf['host']);
        define('DB_USERNAME', $conf['username']);
        define('DB_PASSWORD', $conf['password']);
        define('DB_PORT', $conf['port']);
        define('DB_DATABASE', $conf['database']);

        define('APP_PATH', DOCUMENT_ROOT . '/app/');
        define('LIB_PATH', DOCUMENT_ROOT . '/lib/');
        define('LOCALES_PATH', DOCUMENT_ROOT . '/locales/');
        define('CACHE_PATH', DOCUMENT_ROOT . '/cache/');
        define('PUBLIC_PATH', DOCUMENT_ROOT . '/public/');
        define('PUBLIC_CACHE_PATH', DOCUMENT_ROOT . '/public/cache/');
        define('LOG_PATH', DOCUMENT_ROOT . '/log/');
        define('CONFIG_PATH', DOCUMENT_ROOT . '/config/');

        define('VIEWS_PATH', DOCUMENT_ROOT . '/app/views/');
        define('HELPERS_PATH', DOCUMENT_ROOT . '/app/helpers/');
        define('WIDGETS_PATH', DOCUMENT_ROOT . '/app/widgets/');
        define('MOVIM_SQL_DATE', 'Y-m-d H:i:s');

        define('DEFAULT_PICTURE_FORMAT', 'webp');
        define('DEFAULT_PICTURE_QUALITY', 95);

        define('API_SOCKET', CACHE_PATH . 'socketapi.sock');
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
        if (getenv('baseuri') != null) {
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

        if (substr($uri, -8, 8) == 'picture/') {
            $uri = substr($uri, 0, strlen($uri) - 8);
        }

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
            'charset' => ($conf['type'] == 'mysql') ? 'utf8mb4' : 'utf8',
            'collation' => ($conf['type'] == 'mysql') ? 'utf8mb4_unicode_ci' : 'utf8_unicode_ci',
        ]);

        /**
         * If no SQL request is executed after a few seconds, we close the connection.
         * Eloquent is taking care of reconnecting automatically when a new request is made
         */
        global $loop;

        if ($loop) {
            global $sqlQueryExecuted;

            $loop->addPeriodicTimer(1, function () use ($capsule, &$sqlQueryExecuted) {
                if ($sqlQueryExecuted < time() - 3 /* 3sec */
                && $capsule->getConnection()->getRawPdo() != null) {
                    $capsule->getConnection()->disconnect();
                }
            });

            $dispatcher = new Dispatcher(new Container);
            $dispatcher->listen('Illuminate\Database\Events\QueryExecuted', function ($query) use (&$sqlQueryExecuted) {
                $sqlQueryExecuted = time();
            });

            $capsule->setEventDispatcher($dispatcher);
        }

        $capsule->bootEloquent();
        $capsule->setAsGlobal();
    }

    private function loadCommonLibraries()
    {
        require_once LIB_PATH . 'XMPPtoForm.php';
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

        if (isLogged()) {
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
        set_error_handler([$this, 'systemErrorHandler'], E_ALL);
        set_exception_handler([$this, 'exceptionHandler']);
        register_shutdown_function([$this, 'fatalErrorShutdownHandler']);
    }

    private function setTimezone()
    {
        $offset = 0;

        if (array_key_exists('HTTP_MOVIM_OFFSET', $_SERVER)) $offset = invertSign(((int)$_SERVER['HTTP_MOVIM_OFFSET'])*60);
        elseif (getenv('offset') != 0) $offset = (int)getenv('offset');

        define('TIMEZONE_OFFSET', $offset);
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
            $process = (bool)requestAPI('exists', 2, ['sid' => SESSION_ID]);
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
                requestAPI('disconnect', 2, ['sid' => SESSION_ID]);
            }
        }

        Cookie::set();
    }

    public function getWidgets()
    {
        // Return a list of interesting widgets to load (to save memory)
        return ['Account','AccountNext','AdHoc','Avatar','BottomNavigation',
        'Communities','CommunityAffiliations','CommunityConfig','CommunityData',
        'CommunityHeader','CommunityPosts','CommunitiesServer','CommunitiesServers',
        'Confirm','ContactActions','Chat','ChatOmemo','Chats','Config','ContactData','ContactHeader',
        'ContactSubscriptions','Dialog','Drawer','Init','Location','Login','LoginAnonymous',
        'Menu','Navigation','Notification', 'Notifications','NewsNav','Post','PostActions',
        'Presence','Publish','Rooms','RoomsExplore', 'RoomsUtils', 'Stickers','Toast',
        'Upload','Vcard4','Visio','VisioLink'];
    }

    /**
     * Error Handlers…
     */

    public function systemErrorHandler($errno, string $errstr, string $errfile = '', int $errline = 0, $trace = '')
    {
        if (\is_array($trace)) $trace = '';

        $error = $errstr . " in " . $errfile . ' (line ' . $errline . ")\n" . 'Trace' . "\n" . $trace;

        if (class_exists('Utils')) {
            \Utils::error($error);
        } else {
            echo $error;
        }

        return false;
    }

    public function exceptionHandler($exception)
    {
        $this->systemErrorHandler(
            E_ERROR,
            get_class($exception) . ': '. $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        );
    }

    public function fatalErrorShutdownHandler()
    {
        $lastError = error_get_last();
        if ($lastError && $lastError['type'] === E_ERROR) {
            $this->systemErrorHandler(
                E_ERROR,
                $lastError['message'],
                $lastError['file'],
                $lastError['line']
            );
        }
    }
}
