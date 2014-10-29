<?php
/**
 * @file RPC.php
 * This file is part of Movim
 *
 * @brief Handle incoming requests and call the widgets
 *
 * @author Jaussoin Timothée
 *
 * @version 1.0
 * @date 28 october 2014
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

        $args = array_map(
            function($string) {
                return preg_replace("/[\t\r\n]/", '', trim($string));
            },
            $args);

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

    /**
     * Sends outgoing requests.
     */
    public static function commit()
    {
        return self::$funcalls;
    }

    public static function clear()
    {
        self::$funcalls = array();
    }

    /**
     * Handles incoming requests.
     */
    public function handle_json($json)
    {        
        $request = json_decode($json);

        // Loading the widget.
        $widget_name = (string)$request->widget;

        // Preparing the parameters and calling the function.
        $params = (array)$request->params;

        $result = array();

        $bc = new Bootstrap;
        $bc->loadLanguage();

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

?>
