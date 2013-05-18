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
        
        $args = array_map('trim', $args);

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

        // Just in case (warning)
        if(!is_array(self::$funcalls)) {
            self::$funcalls = array('ping');
        }

        header('Content-Type: application/json');
        printf('%s', json_encode(self::$funcalls));
        
    }

    /**
     * Handles incoming requests.
     */
    public function handle_json()
    {
		if(isset($_GET['do']) && $_GET['do'] == 'poll') {
            moxl\ping();
		} else {
            $json = file_get_contents('php://input');

            $request = json_decode($json);

			// Loading the widget.
			$widget_name = (string)$request->widget;

            // Preparing the parameters and calling the function.
            $params = (array)$request->params;

            $result = array();


            foreach($params as $p) {
                if(is_object($p))
                    array_push($result, (array)$p->container);
                else
                    array_push($result, $p);
            }

            

            /*if($params[0]->container)
                $params = array((array)$params[0]->container);
            elseif(count($params) > 1)
                $params;
            else
                $params = array($params[0]);*/

            $widgets = WidgetWrapper::getInstance(false);
            $widgets->run_widget($widget_name, (string)$request->func, $result);
        }
    }
}

?>
