<?php

use Movim\Controller\Base;

class InfosController extends Base
{
    public function load()
    {
        header('Content-type: application/json');
        $this->set_cookie = false;
        $this->raw = true;
    }
}
