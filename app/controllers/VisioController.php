<?php
use Movim\Controller\Base;

class VisioController extends Base
{
    function load() {
        $this->session_only = true;
        $this->raw = true;
    }

    function dispatch() {
    }
}
