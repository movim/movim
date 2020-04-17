<?php

use Movim\Controller\Base;

class VisioaudioController extends Base
{
    public function load()
    {
        $this->unique = true;
        $this->session_only = true;
    }
}
