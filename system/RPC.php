<?php
if (!defined('DOCUMENT_ROOT')) die('Access denied');
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

        if(self::filter($funcname, $args)) {
            $funcall = array(
                'func' => $funcname,
                'params' => $args,
                );

            self::$funcalls[] = $funcall;
        } elseif(isset($args[0])) {
            //\system\Logs\Logger::log('RPC cleaning system : '.$funcname.', '.$args[0].' cleared');
        }
    }

    /**
     * Check if the event is not already called
     */
    private static function filter($funcname, $args)
    {
        foreach(self::$funcalls as $f) {
            if(isset($f['func']) &&
               isset($f['params']) &&
               $f['func'] == $funcname &&
               $f['params'] === $args)
               return false;
        }

        return true;
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
        $json = file_get_contents('php://input');
        $request = json_decode($json);

        if(isset($_GET['do']) && $_GET['do'] == 'poll') {
            \Moxl\API::ping();
        } elseif((string)$request->widget == 'lazy') {
            $l = new Lazy($request->params[0], $request->params[1]);
        } else {
            // Loading the widget.
            $widget_name = (string)$request->widget;

            // Preparing the parameters and calling the function.
            $params = (array)$request->params;

            $result = array();
            

            foreach($params as $p) {
                if(is_object($p) && isset($p->container))
                    array_push($result, (array)$p->container);
                else
                    array_push($result, $p);
            }

            $widgets = WidgetWrapper::getInstance(false);
            $widgets->runWidget($widget_name, (string)$request->func, $result);
        }
    }
}

?>
