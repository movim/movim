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

class WidgetBase
{
    protected $js = array(); /*< Contains javascripts. */
    protected $css = array(); /*< Contains CSS files. */
    protected $external; /*< Boolean: TRUE if not a system widget. */
    protected $ajax;     /*< Contains ajax client code. */
    protected $tpl;
    protected $user;
    protected $name;
    public $events;
    
    protected $cached;

    /**
     * Initialises Widget stuff.
     * @param external is optional, true if the widget is external (an add-on) to Movim.
     */
    function __construct($external = true)
    {
        // Put default widget init here.
        $this->external = $external;

        $this->ajax = ControllerAjax::getInstance();
        
        $this->user = new User;

        $db = modl\Modl::getInstance();
        $u = new User();
        $db->setUser($u->getLogin());

        // Generating ajax calls.
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
        
        // We load the template engine
        $this->view = new RainTPL;
        $this->view->configure('tpl_dir',      $this->respath('', true));
        $this->view->configure('cache_dir',    CACHE_PATH);
        $this->view->configure('tpl_ext',      'tpl');

        $this->view->assign('c', $this);
                
        $this->name = get_class($this);

        $this->WidgetLoad();
    }
    
    function t() {
        return call_user_func_array('t',func_get_args());
    }
    
    function route() {
        return call_user_func_array('Route::urlize',func_get_args());
    }

    function WidgetLoad()
    {
    }

    /**
     * Generates the widget's HTML code.
     */
    function build()
    {
        echo $this->draw();
    }
    
    /**
     * Return the template's HTML code 
     * @param a specific template name to load (like Ruby partials)
     * @param load the parent template, like for WidgetCommon
     */
    function draw()
    {
        return trim($this->view->draw(strtolower($this->name), true));
    }
    
    protected function tpl() {
        $view = new RainTPL;
                
        $view->configure('tpl_dir', APP_PATH.'widgets/'.$this->name.'/'); 
        $view->configure('cache_dir',    CACHE_PATH);
        $view->configure('tpl_ext',      'tpl'); 
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
        
        $path = '';
        if(!$this->external) {
            $path = 'app/';
        }
        $path .= 'app/widgets/' . $folder . '/' . $file;

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
            $widget = get_class($this);
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
    
    public function isEvents($proto)
    {
        if(is_array($this->events) && 
            array_key_exists($proto['type'], $this->events) &&
            $this->cached == true) {
            return true;
        }
    }

    /**
     * returns the list of javascript files to be loaded for the widget.
     */
    public function loadcss()
    {
        return $this->css;
    }
}

?>
