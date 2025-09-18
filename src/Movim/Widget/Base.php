<?php
/*
 * SPDX-FileCopyrightText: 2010 Jaussoin Timothée
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Movim\Widget;

use App\User;
use Rain\Tpl;
use Movim\Controller\Ajax;
use Movim\Template\Partial;

use Illuminate\Database\Capsule\Manager as DB;

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

    public function __construct(bool $light = false, ?string $view = null)
    {
        if ($view != null) {
            $this->_view = $view;
        }

        $this->me = me();
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
        return __(...$args);
    }

    public function ___(...$args)
    {
        echo call_user_func_array([&$this, '__'], $args);
    }

    public function route(...$args): ?string
    {
        return \Movim\Route::urlize(...$args);
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

        \Movim\RPC::call($funcname, ...$args);
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
