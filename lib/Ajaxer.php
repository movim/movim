<?php

/**
 * @file Ajaxer.php
 * This file is part of PROJECT.
 * 
 * @brief Description
 *
 * @author Guillaume Pasquet <email@addre.ss>
 *
 * @version 1.0
 * @date  8 November 2010
 *
 * Copyright (C)2010 PROPRIETOR
 * 
 * OTHER T&C IF ANY
 */

class Ajaxer extends Controller
{
	protected $funclist = array();
	protected static $instance;

	public function __construct()
	{
		parent::__construct();
	}

	public static function getInstance()
	{
		if(!is_object(self::$instance)) {
			self::$instance = new Ajaxer();
		}
		return self::$instance;
	}

	/**
	 * Generates the javascript part of the ajax.
	 */
	public function genJs()
	{
		if(empty($this->funclist)) {
			return '';
		}
		
		$buffer = '<script type="text/javascript">';
		foreach($this->funclist as $funcdef) {
			$parlist = implode(', ', $funcdef['params']);

            $pardef = $parlist;
            if($pardef != "") {
                $pardef = ", ".$parlist;
            }
			
			$buffer .= "function " . $funcdef['object'] . '_'
				. $funcdef['funcname'] . "(callback, target${pardef}) {";
			$buffer .= "var options = movimPack([$parlist]);";
			$buffer .= "movim_ajaxSend('".$funcdef['object']."', '".$funcdef['funcname']."', callback, target, options);}\n";

		}
		return $buffer . "</script>\n";
	}

	/**
	 * Defines a new function.
	 */
	public function defun($widget, $funcname, array $params)
	{
		$this->funclist[] = array(
			'object' => $widget,
			'funcname' => $funcname,
			'params' => $params,
			);
	}

	/**
	 * Handles incoming ajax requests.
	 */
	public function handle()
	{
		//session_commit();
		if(isset($_GET['do']) && $_GET['do'] == 'poll') {
			$user = new User();
			$xmppSession = XMPPConnect::getInstance($user->getLogin());
			session_commit();
			$xmppSession->pingServer();
			session_commit();
		} else {
			$request = simplexml_load_string(file_get_contents('php://input'));

			// Loading the widget.
			$widget_name = (string)$request['widget'];

            // Preparing the parameters and calling the function.
            $params = array();
            foreach($request->children() as $child) {
                if($child->getName() == 'param') {
                    $params[] = (string)$child;
                }
            }
            
            $widgets = WidgetWrapper::getInstance(false);
            $widgets->run_widget($widget_name, (string)$request['name'], $params);
		}
	}
}

?>
