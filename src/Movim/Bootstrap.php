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
        $this->checkSession();
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
        if (file_exists(DOCUMENT_ROOT . '/.env')) {
            (Dotenv::createUnsafeImmutable(DOCUMENT_ROOT))->safeLoad();
        }

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
        define('CONFIG_PATH', DOCUMENT_ROOT . '/config/');
        define('LOCALES_PATH', DOCUMENT_ROOT . '/locales/');
        define('PUBLIC_CACHE_PATH', DOCUMENT_ROOT . '/public/cache/');
        define('PUBLIC_EMOJIS_PATH', DOCUMENT_ROOT . '/public/emojis/');
        define('PUBLIC_PATH', DOCUMENT_ROOT . '/public/');
        define('PUBLIC_STICKERS_PATH', DOCUMENT_ROOT . '/public/stickers/');
        define('VIEWS_PATH', DOCUMENT_ROOT . '/app/Views/');
        define('WIDGETS_PATH', DOCUMENT_ROOT . '/app/Widgets/');
        define('WORKERS_PATH', DOCUMENT_ROOT . '/workers/');

        define('CACHE_PATH', config('paths.cache'));

        define('MOVIM_SQL_DATE', 'Y-m-d H:i:s');

        define('DEFAULT_PICTURE_FORMAT', 'webp');
        define('DEFAULT_PICTURE_QUALITY', 95);

        define('API_SOCKET', CACHE_PATH . 'socketapi.sock');
        define('AVATAR_HANDLER_SOCKET', CACHE_PATH . 'avatarhandler.sock');
        define('PUSHER_SOCKET', CACHE_PATH . 'pusher.sock');
        define('RESOLVER_SOCKET', CACHE_PATH . 'resolver.sock');
        define('TEMPLATER_SOCKET', CACHE_PATH . 'templater.sock');
    }

    private function getVersion()
    {
        if ($f = fopen(DOCUMENT_ROOT . '/VERSION', 'r')) {
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

        $path = (($dirname == DIRECTORY_SEPARATOR) ? '' : $dirname) . '/';

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
            'collation' => (config('database.driver') == 'mysql') ? 'utf8mb4_bin' : 'utf8_unicode_ci',
        ]);

        /**
         * If no SQL request is executed after a few seconds, we close the connection.
         * Eloquent is taking care of reconnecting automatically when a new request is made
         */
        global $loop;

        if ($loop) {
            global $sqlQueryExecuted;

            $loop->addPeriodicTimer(1, function () use ($capsule, &$sqlQueryExecuted) {
                if (
                    $sqlQueryExecuted < time() - 3 /* 3sec */
                    && $capsule->getConnection()->getRawPdo() != null
                ) {
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

    private function loadHelpers()
    {
        foreach (glob(DOCUMENT_ROOT . '/app/Helpers/*Helper.php') as $file) {
            require_once $file;
        }
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

    private function checkSession()
    {
        if (is_string(SESSION_ID)) {
            $process = (bool)requestAPI('exists', post: ['sid' => SESSION_ID]);
            $session = DBSession::find(SESSION_ID);

            if ($session) {
                $session->loadTimezone();

                // There a session in the DB but no process
                if (!$process) {
                    $session->delete();
                    return;
                }

                $session->loadMemory();
            } elseif ($process) {
                // A process but no session in the db
                requestAPI('disconnect', post: ['sid' => SESSION_ID]);
            }
        }
    }

    public function getWidgets()
    {
        // Return a list of interesting widgets to load (to save memory)
        return [
            'Account',
            'AccountNext',
            'AdHoc',
            'Avatar',
            'Blocked',
            'BottomNavigation',
            'Communities',
            'CommunityAffiliations',
            'CommunityConfig',
            'CommunityData',
            'CommunityHeader',
            'CommunityPosts',
            'CommunitiesServer',
            'CommunitiesServers',
            'Confirm',
            'ContactActions',
            'ContactBlogConfig',
            'Chat',
            'ChatActions',
            'ChatOmemo',
            'Chats',
            'Config',
            'ContactData',
            'ContactHeader',
            'ContactSubscriptions',
            'Dialog',
            'Drawer',
            'EmojisConfig',
            'Login',
            'Menu',
            'Navigation',
            'Notif',
            'Notifications',
            'NewsNav',
            'Post',
            'PostActions',
            'Presence',
            'Publish',
            'PublishStories',
            'Rooms',
            'RoomsExplore',
            'RoomsUtils',
            'Stickers',
            'Stories',
            'Toast',
            'Upload',
            'Vcard4',
            'Visio'
        ];
    }

    /**
     * Error Handlers…
     */

    public function systemErrorHandler($errno, string $errstr, string $errfile = '', int $errline = 0, $trace = '')
    {
        if (\is_array($trace)) $trace = '';

        $error = $errstr . " in " . $errfile . ' (line ' . $errline . ")\n";
        $fullError = $trace != ''
            ? $error . 'Trace' . "\n" . $trace
            : $error;

        if (php_sapi_name() != 'cli' && ob_get_contents() == '') {
            echo 'An error occured during the Movim boot check the ' . config('paths.log') . 'error.log file' . "\n";
        }

        logError($fullError);

        return false;
    }

    public function exceptionHandler($exception)
    {
        $this->systemErrorHandler(
            E_ERROR,
            get_class($exception) . ': ' . $exception->getMessage(),
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
