<?php

/**
 * @file RPC.php
 * This file is part of PROJECT.
 *
 * @brief Description
 *
 * @author Etenil <etenil@etenilsrealm.nl>
 *
 * @version 1.0
 * @date 20 February 2011
 *
 * Copyright (C)2011 Etenil
 *
 * All rights reserved.
 */

class RPC
{
    protected static $instance;
    protected static $funcalls;

    public static function call($funcname)
    {
        if(!is_array(self::$funcalls)) {
            self::$funcalls = array();
        }

        $args = func_get_args();
        array_shift($args);

        $funcall = array(
            'func' => $funcname,
            'params' => $args,
            );

        self::$funcalls[] = $funcall;
    }

    public static function cdata($text)
    {
        $args = func_get_args();
        return '<![CDATA['.
            //call_user_func_array('sprintf', $args).
            $text.
            ']]>';
    }

    /**
	 * Sends outgoing requests.
	 */
    public static function commit()
    {

        // Cleaning rubbish.
        ob_clean();
        ob_start();

        // Starting XML output.
        header('Content-Type: text/xml');
        println('<?xml version="1.0" encoding="UTF-8" ?>');
        println('<movimcontainer>');

        // Just in case (warning)
        if(!is_array(self::$funcalls)) {
            self::$funcalls = array();
        }

        foreach(self::$funcalls as $funcall) {
            println('<funcall name="%s">', $funcall['func']);

            if(is_array($funcall['params'])) {
                foreach($funcall['params'] as $param) {
                    println('<param>%s</param>', $param);
                }
            }

            println('</funcall>');
        }
        println('</movimcontainer>');

        $xml = ob_get_flush();
        file_put_contents('debug', $xml . "\n" . var_export(self::$funcalls, true));
    }

    /**
     * Handles incoming requests.
     */
    public function handle()
    {
		if(isset($_GET['do']) && $_GET['do'] == 'poll') {
			/*$user = new User();
			$xmppSession = Jabber::getInstance($user->getLogin());
			$xmppSession->pingServer();*/
            moxl\ping();
		} else {
            $xml = file_get_contents('php://input');
			$request = simplexml_load_string($xml);

			// Loading the widget.
			$widget_name = (string)$request['widget'];

            // Preparing the parameters and calling the function.
            $params = array();
            foreach($request->children() as $child) {
                if($child->getName() == 'param') {
                    if($child->count() > 0) { // Probably contains an array.
                        $arr = array();
                        foreach($child->children() as $data) {
                            if($data->getName() == 'array') {
                                foreach($data->children() as $elt) {
                                    if($elt->getName() == 'arrayelt') {
                                        if(isset($elt['name'])) {
                                            $arr[(string)$elt['name']] = (string)$elt;
                                        } else {
                                            $arr[] = (string)$elt;
                                        }
                                    }
                                }
                            }
                        }
                        $params[] = $arr;
                    } else {
                        $params[] = (string)$child;
                    }
                }
            }
            
            $widgets = WidgetWrapper::getInstance(false);
            $widgets->run_widget($widget_name, (string)$request['name'], $params);
		}
    }
}

?>
