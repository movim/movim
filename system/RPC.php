<?php
use Movim\Widget\Wrapper;

class RPC
{
    public static function call($funcname)
    {
        $args = func_get_args();
        array_shift($args);

        echo base64_encode(gzcompress(json_encode([
            'func' => $funcname,
            'params' => $args
        ]), 9))."";

        tick();
    }

    /**
     * Handles incoming requests.
     */
    public function handle_json($request)
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
                if(is_object($p) && isset($p->container))
                    array_push($result, (array)$p->container);
                else
                    array_push($result, $p);
            }
        }

        $widgets = Wrapper::getInstance();

        $widgets->runWidget($widget_name, (string)$request->func, $result);
    }
}

?>
