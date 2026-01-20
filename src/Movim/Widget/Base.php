<?php
/*
 * SPDX-FileCopyrightText: 2010 Jaussoin Timothée
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Movim\Widget;

use App\User;
use App\Widgets\Dialog\Dialog;
use App\Widgets\Drawer\Drawer;
use App\Widgets\Notif\Notif;

use Movim\Controller\Ajax;
use Movim\Daemon\Linker\CurrentCall;
use Movim\Template\Partial;

use Moxl\Xec\Action;

use Illuminate\Database\Capsule\Manager as DB;
use Rain\Tpl;

class Base
{
    protected array $js = [];     // Contains javascripts
    protected array $css = [];    // Contains CSS files
    protected $ajax;        // Contains ajax client code
    protected ?string $name = null;
    protected $view;
    public ?User $me = null;

    protected $_view;
    private $_useTemplater = false;

    public $baseUri;
    public array $events = [];
    public array $tasks = [];
    public $filters;

    // Meta tags
    public $title;
    public $image;
    public $description;

    public function __construct(
        ?User $user,
        bool $light = false,
        ?string $view = null,
        public ?string $sessionId = null
    ) {
        if ($view != null) {
            $this->_view = $view;
        }

        $this->me = $user;
        $this->setName();
        $this->load();
        $this->baseUri = BASE_URI;

        // If light loading enabled, we stop here
        if ($light) {
            return;
        }

        if (php_sapi_name() != 'cli') {
            // Put default widget init here.
            $this->ajax = Ajax::getInstance();

            if (!$this->ajax->isRegistered($this->name)) {
                // Generating Ajax calls.
                $refl = new \ReflectionClass('App\\Widgets\\' . $this->name . '\\' . $this->name);
                $meths = $refl->getMethods();

                foreach ($meths as $method) {
                    if (preg_match('#^ajax#', $method->name)) {
                        $pars = $method->getParameters();
                        $params = [];
                        foreach ($pars as $param) {
                            $params[] = $param->name;
                        }

                        $this->ajax->defineFunction(
                            $this->name,
                            $method->name,
                            $params
                        );
                    }
                }

                $this->ajax->register($this->name);
            }

            $config = [
                'tpl_dir'       => $this->respath('', true),
                'cache_dir'     => CACHE_PATH,
                'tpl_ext'       => 'tpl',
                'auto_escape'   => true
            ];

            // We load the template engine
            $this->view = new Tpl;
            $this->view->objectConfigure($config);

            $this->view->assign('c', $this);
        }
    }

    public function __destruct()
    {
        unset($this->view);
        unset($this->ajax);
        unset($this->_view);
    }

    public function __(...$args)
    {
        if ($this->sessionId) {
            $args = func_get_args();
            $string = array_shift($args);
            return linker($this->sessionId)->locale->translate($string, $args);
        }

        return __(...$args);
    }

    public function xmpp(Action $action)
    {
        $action->attachSession($this->sessionId);
        $action->attachUser($this->me);
        return $action;
    }

    public function notif(?string $rpcCall = null, ...$args)
    {
        $notif = new Notif($this->me, sessionId: $this->sessionId);

        if ($rpcCall) {
            $notif->rpcCall($rpcCall);
        }

        $notif->append(...$args);
    }

    public function currentCall(): ?CurrentCall
    {
        return $this->sessionId
            ? linker($this->sessionId)?->currentCall
            : null;
    }

    public function dialog(...$args)
    {
        (new Dialog($this->me))->fill(...$args);
    }

    public function drawer(...$args)
    {
        (new Drawer($this->me))->fill(...$args);
    }

    public function route(...$args): ?string
    {
        return \Movim\Route::urlize(...$args);
    }

    /**
     * Return a human-readable date
     *
     * @param timestamp $string
     * @return string
     */
    function prepareDate(string $datetime = '', bool $compact = false, bool $hours = true): string
    {
        $time = strtotime($datetime);
        $time = $time !== false ? $time : time();
        $t = $time + getTimezoneOffset($this->resolveTimezone());

        $date = '';

        $reldays = - (time() - $t - (time() % 86400)) / 86400;

        // if $reldays is within a week
        if (-7 < $reldays && $reldays <= 2) {
            if ($reldays > 1) {
                $date = '';
            } elseif (-1 < $reldays && $reldays <= 0) {
                $date = $this->__('date.yesterday');
            } elseif (0 < $reldays && $reldays <= 1) {
                // Today
            } else {
                $date = $this->__('date.ago', ceil(-$reldays));
            }
        } else {
            if (!$compact) {
                $date .= $this->__('day.' . strtolower(date('l', $t))) . ', ';
            }

            $date .= date('j', $t) . ' ' . $this->__('month.' . strtolower(date('F', $t)));

            // Over 6 months
            if (abs($reldays) > 182) {
                $date .= gmdate(', Y', $t);
            }

            if ($compact) {
                return $date;
            }
        }

        //if $hours option print the time
        if ($hours) {
            if ($date != '') {
                $date .= ' - ';
            }

            $date .= gmdate('H:i', $t);
        }

        return $date;
    }

    /**
     * Return a human-readable time
     *
     * @param timestamp $string
     * @return string
     */
    function prepareTime(string $datetime = ''): string
    {
        $time = strtotime($datetime);
        $time = $time != false ? $time : time();
        $t = $time + getTimezoneOffset($this->resolveTimezone());

        return gmdate('H:i', $t);
    }

    private function resolveTimezone(): string
    {
        if (defined('SESSION_TIMEZONE')) return SESSION_TIMEZONE;

        return $this->sessionId
            ? linker($this->sessionId)->timezone ?? date_default_timezone_get()
            : 'UTC';
    }

    public function toast($title, int $timeout = 3000)
    {
        $this->rpc('Toast.send', $title, $timeout);
    }

    public function database(string $driver): bool
    {
        return DB::getDriverName() == $driver;
    }

    public function rpc($funcname, ...$args)
    {
        if ($this->_useTemplater) {
            $payload = [
                'func' => $funcname,
                'templater' => true,
                'sid' => $this->me->session->id,
            ];

            if (!empty($args)) {
                $payload['p'] = $args;
            }

            writeTemplater($payload);
            return;
        }

        (new \Movim\RPC(
            user: $this->me,
            sessionId: $this->sessionId
        ))->call($funcname, ...$args);
    }

    public function enableUseTemplater()
    {
        $this->_useTemplater = true;
    }

    public function boot() {}

    public function load() {}

    /**
     * Generates the widget's HTML code.
     */
    public function build(...$params): string
    {
        return $this->draw(...$params);
    }

    /**
     * Get the current view name
     */
    public function getView()
    {
        return $this->_view;
    }

    /**
     *  @desc Return the template's HTML code
     */
    public function draw(...$params): string
    {
        if (method_exists($this, 'display')) {
            $this->display(...$params);
        }

        return (file_exists($this->respath(strtolower($this->name) . '.tpl', true)))
            ? trim((string)$this->view->draw(strtolower($this->name), true))
            : '';
    }

    protected function tpl(): Partial
    {
        return new Partial($this);
    }

    protected function view(string $template, ?array $assign = []): ?string
    {
        $view = $this->tpl();

        foreach ($assign as $key => $value) {
            $view->assign($key, $value);
        }

        return $view->draw($template);
    }

    /**
     * @brief Returns the path to the specified widget file.
     * @param file is the file's name to make up the path for.
     * @param fspath is optional, returns the OS path if true, the URL by default.
     */
    protected function respath(
        string $file,
        bool $fspath = false,
        bool $parent = false,
        bool $notime = false
    ): string {
        $folder = ($parent == false)
            ? (new \ReflectionClass($this))->getShortName()
            : (new \ReflectionClass($this))->getParentClass()->getShortName();

        $path = 'app/Widgets/' . $folder . '/' . $file;

        if ($fspath) {
            $path = DOCUMENT_ROOT . '/' . $path;
        } else {
            $path = urilize($path, $notime);
        }

        return $path;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * @brief returns the list of javascript files to be loaded for the widget.
     */
    public function loadjs(): array
    {
        return $this->js;
    }

    /**
     * @brief Adds a CSS file to this widget.
     */
    protected function addcss(string $filename)
    {
        $this->css[] = $this->cacheFile($filename);
    }

    /**
     * @brief Adds a javascript file to this widget.
     */
    protected function addjs(string $filename)
    {
        $this->js[] = $this->cacheFile($filename);
    }

    /**
     * @brief Cache and return the publicly cached file
     */
    private function cacheFile(string $filename)
    {
        $this->setName();

        $local = DOCUMENT_ROOT . '/app/Widgets/' . $this->name . '/' . $filename;
        $cache = PUBLIC_CACHE_PATH . $this->name . '_' . $filename;
        $path = 'cache/' . $this->name . '_' . $filename;

        if (!\file_exists($cache)) {
            \symlink($local, $cache);
        }

        return urilize($path);
    }

    /**
     * @brief Set the current widget name
     */
    private function setName()
    {
        if ($this->name == null) {
            $this->name = (new \ReflectionClass($this))->getShortName();
        }
    }

    /**
     * @brief returns the list of javascript files to be loaded for the widget.
     */
    public function loadcss(): array
    {
        return $this->css;
    }

    /*
     * @brief Fetch and return get variables
     */
    protected function get(string $name): ?string
    {
        if (isset($_GET[$name])) {
            return htmlentities(urldecode($_GET[$name]));
        }

        return null;
    }

    /**
     * @brief Registers an event handler.
     * @param $key The event key
     * @param $method The function to call
     * @param $filter Only call this function if the session notif_key is good
     */
    protected function registerEvent(string $key, string $method, ?string $filter = null)
    {
        if (!array_key_exists($key, $this->events)) {
            $this->events[$key] = [$method];
        } else {
            $this->events[$key][] = $method;
        }

        if ($filter != null) {
            if (!is_array($this->filters)) {
                $this->filters = [];
            }

            $this->filters[$key . '_' . $method] = $filter;
        }
    }
}
