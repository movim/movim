<?php

namespace Movim\Widget;

use Rain\Tpl;
use Movim\Controller\Ajax;
use Movim\Template\Partial;

class Base
{
    protected $js = [];     // Contains javascripts
    protected $css = [];    // Contains CSS files
    protected $ajax;        // Contains ajax client code
    protected $user;
    protected $name;

    protected $pure;        // To render the widget without the container

    protected $_view;

    public $events;
    public $filters;

    // Meta tags
    public $title;
    public $image;
    public $description;

    public function __construct(bool $light = false, string $view = null)
    {
        if ($view != null) {
            $this->_view = $view;
        }

        $this->user = \App\User::me();
        $this->load();
        $this->name = get_class($this);

        // If light loading enabled, we stop here
        if ($light) {
            return;
        }

        // Put default widget init here.
        $this->ajax = Ajax::getInstance();

        if (!$this->ajax->isRegistered($this->name)) {
            // Generating Ajax calls.
            $refl = new \ReflectionClass($this->name);
            $meths = $refl->getMethods();

            foreach ($meths as $method) {
                if (preg_match('#^ajax#', $method->name)) {
                    $pars = $method->getParameters();
                    $params = [];
                    foreach ($pars as $param) {
                        $params[] = $param->name;
                    }

                    $this->ajax->defun(
                        $this->name,
                        $method->name,
                        $params
                    );
                }
            }
        }

        if (php_sapi_name() != 'cli') {
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

            $this->pure = false;
        }
    }

    public function __destruct()
    {
        unset($this->view);
        unset($this->ajax);
        unset($this->user);
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

    public function route(...$args): string
    {
        return \Movim\Route::urlize(...$args);
    }

    public function rpc(...$args)
    {
        \Movim\RPC::call(...$args);
    }

    public function load()
    {
    }

    /**
     * Generates the widget's HTML code.
     */
    public function build(): string
    {
        return $this->draw();
    }

    /**
     * Send an event to the Widgets
     */
    public function event(string $key, $data = null)
    {
        $e = new Event;
        $e->run($key, $data);
    }

    /**
     * Get the current view name
     */
    public function getView()
    {
        return $this->_view;
    }

    /**
     * Get the current user
     */
    public function getUser()
    {
        return $this->user;
    }

    /*
     * @desc Preload some sourcecode for the draw method
     */
    public function display()
    {
    }

    /**
     *  @desc Return the template's HTML code
     */
    public function draw(): string
    {
        $this->display();

        return (file_exists($this->respath(strtolower($this->name).'.tpl', true)))
            ? trim($this->view->draw(strtolower($this->name), true))
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
            ? get_class($this)
            : get_parent_class($this);

        $path = 'app/widgets/' . $folder . '/' . $file;

        if ($fspath) {
            $path = DOCUMENT_ROOT . '/'.$path;
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
        $local = WIDGETS_PATH . get_class($this) . '/' . $filename;
        $cache = PUBLIC_CACHE_PATH . get_class($this) . '_' . $filename;
        $path = 'cache/' . get_class($this) . '_' . $filename;

        if (!\file_exists($cache)) {
            \symlink($local, $cache);
        }

        return urilize($path);
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
    protected function get(string $name)
    {
        if (isset($_GET[$name])) {
            return htmlentities(urldecode($_GET[$name]));
        }
    }

    /**
     * @brief Registers an event handler.
     * @param $key The event key
     * @param $method The function to call
     * @param $filter Only call this function if the session notif_key is good
     */
    protected function registerEvent(string $key, string $method, $filter = null)
    {
        if (!is_array($this->events)
        || !array_key_exists($key, $this->events)) {
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
