<?php
use Movim\Controller\Base;

class SystemController extends Base
{
    public function load()
    {
        $this->session_only = false;
        $this->raw = true;
    }

    public function dispatch()
    {
    }
}
