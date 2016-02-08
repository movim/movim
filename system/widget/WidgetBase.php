<?php

/**
 * @file Widget.php
 * This file is part of MOVIM.
 *
 * @brief A widget interface.
 *
 * @author Guillaume Pasquet <etenil@etenilsrealm.nl>
 *
 * @version 1.0
 * @date 20 October 2010
 *
 * Copyright (C)2010 MOVIM Project
 *
 * See COPYING for licensing information.
 */

use Rain\Tpl;

class WidgetBase
{
    protected $js = array(); /*< Contains javascripts. */
    protected $css = array(); /*< Contains CSS files. */
    protected $rawcss = array(); /*< Contains raw CSS files links. */
    protected $ajax;     /*< Contains ajax client code. */
    protected $user;
    protected $name;
    protected $pure;    // To render the widget without the container
    //protected $translations = array(); // Set translations in the controller
    protected $_view;
    public $events;
    public $filters;

    // Meta tags
    public $title;
    public $image;
    public $description;

    /**
     * Initialises Widget stuff.
     */
    function __construct($light = false, $view = null)
    {
        if($view != null) $this->_view = $view;

        $this->user = new User;

        $this->load();
        $this->name = get_class($this);

        // If light loading enabled, we stop here
        if($light)
            return;

        // Put default widget init here.
        $this->ajax = AjaxController::getInstance();

        // Generating Ajax calls.
        $refl = new ReflectionClass($this->name);
        $meths = $refl->getMethods();

        foreach($meths as $method) {
            if(preg_match('#^ajax#', $method->name)) {
                $pars = $method->getParameters();
                $params = array();
                foreach($pars as $param) {
                    $params[] = $param->name;
                }

                $this->ajax->defun($this->name, $method->name, $params);
            }
        }

        $config = array(
            'tpl_dir'       => $this->respath('', true),
            'cache_dir'     => CACHE_PATH,
            'tpl_ext'       => 'tpl',
            'auto_escape'   => false
        );
        // We load the template engine
        $this->view = new Tpl;
        $this->view->objectConfigure($config);

        $this->view->assign('c', $this);

        $this->pure = false;
    }

    function __destruct()
    {
        unset($this->view);
        unset($this->ajax);
        unset($this->user);
        unset($this->_view);
    }

    function __()
    {
        $args = func_get_args();
        return call_user_func_array('__', $args);
    }

    function ___()
    {
        echo call_user_func_array(array(&$this, '__'), func_get_args());
    }

    function supported($key)
    {
        return $this->user->isSupported($key);
    }

    function route()
    {
        return call_user_func_array('Route::urlize',func_get_args());
    }

    function load() {}

    /**
     * Generates the widget's HTML code.
     */
    function build()
    {
        return $this->draw();
    }

    /**
     * Get the current view name
     */
    function getView()
    {
        return $this->_view;
    }

    /*
     * @desc Preload some sourcecode for the draw method
     */
    function display() {}

    /**
     *  @desc Return the template's HTML code
     */
    function draw()
    {
        $this->display();
        return trim($this->view->draw(strtolower($this->name), true));
    }

    protected function tpl() {
        $config = array(
            'tpl_dir'       => APP_PATH.'widgets/'.$this->name.'/',
            'cache_dir'     => CACHE_PATH,
            'tpl_ext'       => 'tpl',
            'auto_escape'   => false
        );

        $view = new Tpl;
        $view->objectConfigure($config);
        $view->assign('c', $this);

        return $view;
    }

    /**
     * @brief Returns the path to the specified widget file.
     * @param file is the file's name to make up the path for.
     * @param fspath is optional, returns the OS path if true, the URL by default.
     */
    protected function respath($file, $fspath = false, $parent = false)
    {
        if($parent == false)
            $folder = get_class($this);
        else
            $folder = get_parent_class($this);

        $path = 'app/widgets/' . $folder . '/' . $file;

        if($fspath) {
            $path = DOCUMENT_ROOT . '/'.$path;
        } else {
            $path = BASE_URI . $path;
        }

        return $path;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * @brief Calls an the ajax function of another widget.
     */
    protected function callWidget($widgetname, $funcname)
    {
        $params = func_get_args();
        echo $this->makeCall(array_slice($params, 1), $widgetname);
    }

    /**
     * @brief Returns the javascript ajax call.
     */
    protected function call($funcname)
    {
        return $this->makeCall(func_get_args());
    }

    /**
     * @brief Returns the javascript call to another widget's ajax function.
     */
    protected function genCallWidget($widgetname, $funcname)
    {
        $params = func_get_args();
        return $this->makeCall(array_slice($params, 1), $widgetname);
    }

    protected function makeCall($params, $widget=false)
    {
        if(!$widget) {
            $widget = $this->name;
        }

        $funcname = array_shift($params);
        $args = implode(', ', $params);

        return $widget . '_' . $funcname . "(" . $args . ");";
    }

    /**
     * @brief Adds a javascript file to this widget.
     */
    protected function addjs($filename)
    {
        $this->js[] = $this->respath($filename);
    }

    /**
     * @brief returns the list of javascript files to be loaded for the widget.
     */
    public function loadjs()
    {
        return $this->js;
    }

    /**
     * @brief Adds a CSS file to this widget.
     */
    protected function addcss($filename)
    {
        $this->css[] = $this->respath($filename);
    }

    /**
     * @brief Adds a CSS to the page.
     */
    protected function addrawcss($url)
    {
        $this->rawcss[] = $url;
    }

    /**
     * @brief returns the list of javascript files to be loaded for the widget.
     */
    public function loadcss()
    {
        return array_merge($this->css, $this->rawcss);
    }

    /*
     * @brief Fetch and return get variables
     */
    protected function get($name)
    {
        if(isset($_GET[$name])) {
            return htmlentities($_GET[$name]);
        } else {
            return false;
        }
    }

    /**
     * @brief Registers an event handler.
     * @param $type The event key
     * @param $function The function to call
     * @param $filter Only call this function if the session notif_key is good
     */
    protected function registerEvent($type, $function, $filter = null)
    {
        if(!is_array($this->events)
        || !array_key_exists($type, $this->events)) {
            $this->events[$type] = array($function);
        } else {
            $this->events[$type][] = $function;
        }

        if($filter != null) {
            if(!is_array($this->filters)) {
                $this->filters = array();
            }
            $this->filters[$function] = $filter;
        }
    }
}

?>
