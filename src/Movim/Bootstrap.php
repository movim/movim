<?php
/*
 * SPDX-FileCopyrightText: 2010 Jaussoin Timothée
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Movim;

define('DOCUMENT_ROOT', dirname(__FILE__, 3));

use App\Configuration;
use Illuminate\Database\Capsule\Manager as Capsule;

use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

use Dotenv\Dotenv;

use App\Session as DBSession;
use App\User as DBUser;

class Bootstrap
{
    public function boot($dbOnly = false)
    {
        $this->loadHelpers();
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

        $this->setTimezone();
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

        if (!file_exists(config('paths.log')) && !@mkdir(config('paths.log'))) {
            throw new \Exception('Couldn’t create log directory');
        }
    }

    private function setConstants()
    {
        (Dotenv::createImmutable(DOCUMENT_ROOT))->load();

        define('APP_TITLE', 'Movim');
        define('APP_NAME', 'movim');
        define('APP_VERSION', $this->getVersion());
        define('SMALL_PICTURE_LIMIT', 768000);

        if (isset($_SERVER['HTTP_HOST'])) {
            define('BASE_HOST', $_SERVER['HTTP_HOST']);
        }

        if (isset($_SERVER['SERVER_NAME'])) {
            define('BASE_DOMAIN', $_SERVER["SERVER_NAME"]);
        }

        define('BASE_URI', $this->getBaseUri());
        define('SESSION_ID', $_COOKIE['MOVIM_SESSION_ID'] ?? getenv('sid'));

        define('APP_PATH', DOCUMENT_ROOT . '/app/');
        define('LOCALES_PATH', DOCUMENT_ROOT . '/locales/');
        define('PUBLIC_PATH', DOCUMENT_ROOT . '/public/');
        define('PUBLIC_CACHE_PATH', DOCUMENT_ROOT . '/public/cache/');
        define('CONFIG_PATH', DOCUMENT_ROOT . '/config/');
        define('VIEWS_PATH', DOCUMENT_ROOT . '/app/views/');
        define('WIDGETS_PATH', DOCUMENT_ROOT . '/app/widgets/');

        define('CACHE_PATH', config('paths.cache'));

        define('MOVIM_SQL_DATE', 'Y-m-d H:i:s');

        define('DEFAULT_PICTURE_FORMAT', 'webp');
        define('DEFAULT_PICTURE_QUALITY', 95);

        define('API_SOCKET', CACHE_PATH . 'socketapi.sock');
    }

    private function getVersion()
    {
        if ($f = fopen(DOCUMENT_ROOT.'/VERSION', 'r')) {
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
        $capsule = new Capsule;
        $capsule->addConnection([
            'driver'    => config('database.driver'),
            'host'      => config('database.host'),
            'port'      => config('database.port'),
            'database'  => config('database.database'),
            'username'  => config('database.username'),
            'password'  => config('database.password'),
            'charset'   => (config('database.driver') == 'mysql') ? 'utf8mb4' : 'utf8',
            'collation' => (config('database.driver') == 'mysql') ? 'utf8mb4_unicode_ci' : 'utf8_unicode_ci',
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
        require_once DOCUMENT_ROOT . '/lib/XMPPtoForm.php';
        require_once DOCUMENT_ROOT . '/lib/SDPtoJingle.php';
        require_once DOCUMENT_ROOT . '/lib/JingletoSDP.php';
    }

    private function loadHelpers()
    {
        foreach (glob(DOCUMENT_ROOT.'/app/helpers/*Helper.php') as $file) {
            require_once $file;
        }
    }

    private function loadDispatcher()
    {
        require_once APP_PATH . 'widgets/Notif/Notif.php';
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
        'ContactSubscriptions','Dialog','Drawer','Location','Login',
        'Menu','Navigation','Notif', 'Notifications','NewsNav','Post','PostActions',
        'Presence','Publish','Rooms','RoomsExplore', 'RoomsUtils', 'Stickers','Toast',
        'Upload','Vcard4','Visio','VisioLink'];
    }

    /**
     * Error Handlers…
     */

    public function systemErrorHandler($errno, string $errstr, string $errfile = '', int $errline = 0, $trace = '')
    {
        if (\is_array($trace)) $trace = '';

        $error = $errstr . " in " . $errfile . ' (line ' . $errline . ")\n";
        $fullError = $error . 'Trace' . "\n" . $trace;

        if (php_sapi_name() != 'cli' && ob_get_contents() == '') {
            echo 'An error occured during the Movim boot check the ' . config('paths.log') . 'error.log file' . "\n";
        }

        if (php_sapi_name() == 'cli' && !class_exists('Utils')) {
            echo $error;
        } else {
            \Utils::error($fullError);
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
