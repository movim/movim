<?php

use Movim\Controller\Base;

class VisioController extends Base
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
