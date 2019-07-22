<?php

use Movim\Controller\Base;

class InfosController extends Base
{
    public function load()
    {
        header('Content-type: application/json');
        $this->raw = true;
    }
}
