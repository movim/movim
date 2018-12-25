<?php

use Movim\Controller\Base;

class InfosController extends Base
{
    public function load()
    {
        header('Content-type: application/json');
        $this->session_only = false;
        $this->raw = true;
    }

    public function dispatch()
    {
    }
}
