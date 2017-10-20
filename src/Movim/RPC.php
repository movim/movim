<?php

namespace Movim;

use Movim\Widget\Wrapper;

class RPC
{
    protected static $funcall;

    public static function call($funcname)
    {
        $args = func_get_args();
        array_shift($args);

        self::$funcall = [
            'func' => $funcname,
            'params' => $args,
        ];

        writeOut();
    }

    /**
     * Sends outgoing requests.
     */
    public static function commit()
    {
        return self::$funcall;
    }

    /**
     * Handles incoming requests.
     */
    public function handleJSON($request)
    {
        // Loading the widget.
        if(isset($request->widget)) {
            $widget_name = (string)$request->widget;
        } else {
            return;
        }

        $result = [];

        // Preparing the parameters and calling the function.
        if(isset($request->params)) {
            $params = (array)$request->params;

            foreach($params as $p) {
                if(is_object($p) && isset($p->container)) {
                    array_push($result, (array)$p->container);
                } else {
                    array_push($result, $p);
                }
            }
        }

        $widgets = new Wrapper;
        $widgets->runWidget($widget_name, (string)$request->func, $result);
    }
}

?>
