<?php
use Movim\Controller\Base;

class FeedController extends Base
{
    function load() {
        $this->session_only = false;
        $this->raw = true;
    }

    function dispatch() {
    }
}
