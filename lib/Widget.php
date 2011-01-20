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

class Widget
{
	protected $js = array(); /*< Contains javascripts. */
	protected $css = array(); /*< Contains CSS files. */
	protected $external; /*< Boolean: TRUE if not a system widget. */
	protected $ajax;	 /*< Contains ajax client code. */
	protected $xmpp; /*< XMPPConnect instance. */
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
		$this->xmpp = XMPPConnect::getInstance();
		$this->ajax = Ajaxer::getInstance();

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
		// Initialize stuff here.
	}

	/**
	 * Generates the widget's HTML code.
	 */
	function build()
	{
		echo t("This is a sample widget.");
	}

	/**
	 * Returns the path to the specified widget file.
	 * @param file is the file's name to make up the path for.
	 * @param fspath is optional, returns the OS path if true, the URL by default.
	 */
	protected function respath($file, $fspath = false)
	{
		return ($fspath? BASE_PATH : BASE_URI) .
			($this->external? 'widgets/' . $file : $file);
	}

	protected function callAjax($funcname, $mode, $modeopt)
	{
		$args = implode(', ', array_slice(func_get_args(), 3));
		if($args != "") {
			$args = ', ' . $args;
		}

		echo get_class($this) . '_' . $funcname . "($mode, $modeopt" . $args . ");";

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
	public function runEvents($type, $event)
	{
		if(is_array($this->events) && array_key_exists($type, $this->events)) {
			foreach($this->events[$type] as $handler) {
				call_user_func(array($this, $handler), $event);
			}
		}
	}

	/**
	 * returns the list of javascript files to be loaded for the widget.
	 */
	public function loadcss()
	{
		return $this->css;
	}

    /**
     * Prints out a widget content in XML to be handled by javascript for
     * ajax return.
     */
    protected function sendto($id, $method, $payload)
    {
        ob_clean();
        header('Content-Type: text/xml');
        echo '<?xml version="1.0" encoding="UTF-8" ?>';
        echo '<movim>';
        echo '<target method="' . $method . '">';
        echo $id;
        echo '</target>';
        echo '<payload>';
        echo '<![CDATA[' . $payload . "]]>";
        echo "</payload>";
        echo "</movim>";
    }
}

?>
