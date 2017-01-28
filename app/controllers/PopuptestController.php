<?php

use Movim\Controller\Base;

class PopuptestController extends Base
{
    function load()
    {
        $this->unique = true;
        $this->session_only = true;
    }

    function dispatch()
    {
    }
}
