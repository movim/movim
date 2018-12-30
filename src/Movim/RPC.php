<?php

namespace Movim;

use Movim\Widget\Wrapper;

class RPC
{
    public static function call($funcname, ...$args)
    {
        writeOut([
            'func' => $funcname,
            'params' => $args,
        ]);
    }

    /**
     * Handles incoming requests.
     */
    public function handleJSON($request)
    {
        if (!isset($request->widget)) return;
        $start = microtime(true);
        (new Wrapper)->runWidget(
            (string)$request->widget,
            (string)$request->func,
            (array)$request->params
        );
        $time_elapsed_secs = microtime(true) - $start;
        \Utils::debug($request->widget.' '.$request->func. ':'. round($time_elapsed_secs*1000));
    }
}
