<?php

use Movim\Controller\Base;

class VisioController extends Base
{
    public function load()
    {
        $this->unique = true;
        $this->session_only = true;
    }
}
