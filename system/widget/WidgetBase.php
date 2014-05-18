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
    protected $ajax;     /*< Contains ajax client code. */
    protected $tpl;
    protected $user;
    protected $name;
    protected $pure;    // To render the widget without the container
    protected $translations = array(); // Set translations in the controller
    public $events;

    /**
     * Initialises Widget stuff.
     */
    function __construct()
    {
        // Put default widget init here.
        $this->ajax = AjaxController::getInstance();
        
        $this->user = new User;

        // Generating Ajax calls.
        $refl = new ReflectionClass(get_class($this));
        $meths = $refl->getMethods();

        foreach($meths as $method) {
            if(preg_match('#^ajax#', $method->name)) {
                $pars = $method->getParameters();
                $params = array();
                foreach($pars as $param) {
                    $params[] = $param->name;
                }

                $this->ajax->defun(get_class($this), $method->name, $params);
            }
        }

        $config = array(
            'tpl_dir'       => $this->respath('', true),
            'cache_dir'     => CACHE_PATH,
            'tpl_ext'       => 'tpl',
            'auto_escape'   => false
        );
        

        if(file_exists($this->respath('locales.ini', true))) {
            $this->translations = parse_ini_file($this->respath('locales.ini', true));
        }

        // We load the template engine
        $this->view = new Tpl;
        $this->view->objectConfigure($config);

        $this->view->assign('c', $this);
                
        $this->name = get_class($this);
        
        $this->pure = false;

        $this->load();
    }
    
    function t() {
        return call_user_func_array('t',func_get_args());
    }
    
    function __() {
        $args = func_get_args();
        if(array_key_exists($args[0], $this->translations)) {
            $args[0] = $this->translations[$args[0]];
            return call_user_func_array(array(&$this, 't'), $args);
        } 
        
        global $translationshash;
        
        if(array_key_exists($args[0], $translationshash)) {
            return call_user_func_array('__', $args);
        }
        
        return $args[0];
    }
    
    function route() {
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

    /*
     * @desc Preload some sourcecode for the draw method
     */
    function display() {}
    
    /**
     * Return the template's HTML code 
     * @param a specific template name to load (like Ruby partials)
     * @param load the parent template, like for WidgetCommon
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
     * Returns the path to the specified widget file.
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
     * Generates and print an ajax call.
     */
    protected function callAjax($funcname)
    {
        echo $this->makeCallAjax(func_get_args());
    }

    /**
     * Calls an the ajax function of another widget.
     */
    protected function callWidget($widgetname, $funcname)
    {
        $params = func_get_args();
        echo $this->makeCallAjax(array_slice($params, 1), $widgetname);
    }

    /**
     * Returns the javascript ajax call.
     */
    protected function genCallAjax($funcname)
    {
        return $this->makeCallAjax(func_get_args());
    }

    /**
     * Returns the javascript call to another widget's ajax function.
     */
    protected function genCallWidget($widgetname, $funcname)
    {
        $params = func_get_args();
        return $this->makeCallAjax(array_slice($params, 1), $widgetname);
    }

    protected function makeCallAjax($params, $widget=false)
    {
        if(!$widget) {
            $widget = $this->name;
        }

        $funcname = array_shift($params);
        $args = implode(', ', $params);

        return $widget . '_' . $funcname . "(" . $args . ");";
    }

    /**
     * Adds a javascript file to this widget.
     */
    protected function addjs($filename)
    {
        $this->js[] = $this->respath($filename);
    }

    /**
     * returns the list of javascript files to be loaded for the widget.
     */
    public function loadjs()
    {
        return $this->js;
    }

    /**
     * Adds a javascript file to this widget.
     */
    protected function addcss($filename)
    {
        $this->css[] = $this->respath($filename);
    }
    
    /**
     * returns the list of javascript files to be loaded for the widget.
     */
    public function loadcss()
    {
        return $this->css;
    }

    /**
     * Registers an event handler.
     */
    protected function registerEvent($type, $function)
    {
        if(!is_array($this->events)
        || !array_key_exists($type, $this->events)) {
            $this->events[$type] = array($function);
        } else {
            $this->events[$type][] = $function;
        }
    }

    /**
     * Runs all events of a given type.
     */
    public function runEvents($proto)
    {
        if(is_array($this->events) && array_key_exists($proto['type'], $this->events)) {
            $returns = array();

            foreach($this->events[$proto['type']] as $handler) {
                $returns[] = call_user_func(array($this, $handler), $proto['data']);
            }

            return $returns;
        }
    }
}

?>
