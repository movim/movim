<?php

use Movim\Controller\Base;

class PopuptestController extends Base
{
    public function load()
    {
        $this->unique = true;
        $this->session_only = true;
    }
}
