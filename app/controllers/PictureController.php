<?php
use Movim\Controller\Base;

class PictureController extends Base
{
    function load()
    {
        $this->session_only = true;
        $this->raw = true;
    }

    function dispatch()
    {
    }
}
