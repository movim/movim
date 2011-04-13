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
	protected $ajax;	 /*< Contains ajax client code. */
	protected $xmpp; /*< Jabber instance. */
	protected $name;
	protected $events;

	/**
	 * Initialises Widget stuff.
	 * @param external is optional, true if the widget is external (an add-on) to Movim.
	 */
	function __construct($external = true)
	{
		// Put default widget init here.
		$this->external = $external;
		$this->xmpp = Jabber::getInstance();
		$this->ajax = ControllerAjax::getInstance();

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

		$this->WidgetLoad();
	}

    function WidgetLoad()
    {
    }

	/**
	 * Generates the widget's HTML code.
	 */
    function build()
    {
    }

	/**
	 * Returns the path to the specified widget file.
	 * @param file is the file's name to make up the path for.
	 * @param fspath is optional, returns the OS path if true, the URL by default.
	 */
	protected function respath($file, $fspath = false)
	{
        $path = "";
        if(!$this->external) {
            $path = 'system/';
        }
        $path .= 'Widget/widgets/' . get_class($this) . '/' . $file;

        if($fspath) {
            $path = BASE_PATH . $path;
        } else {
            $path = BASE_URI . $path;
        }

        return $path;
	}

	protected function callAjax($funcname)
	{
		echo $this->makeCallAjax(func_get_args());
	}

	protected function genCallAjax($funcname)
	{
		return $this->makeCallAjax(func_get_args());
	}

    protected function makeCallAjax($params)
    {
        $funcname = array_shift($params);
        $args = implode(', ', $params);

		return get_class($this) . '_' . $funcname . "(" . $args . ");";
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

	/**
	 * returns the list of javascript files to be loaded for the widget.
	 */
	public function loadcss()
	{
		return $this->css;
	}
}

?>
