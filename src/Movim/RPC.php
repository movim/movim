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
        if (!isset($request->w)) {
            return;
        }

        (new Wrapper)->runWidget(
            (string)$request->w,
            (string)$request->f,
            isset($request->p) ? (array)$request->p : []
        );
    }
}
