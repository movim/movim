<?php
use Movim\Controller\Base;

class PostController extends Base
{
    function load()
    {
        $this->session_only = true;
    }

    function dispatch()
    {
        $this->page->setTitle(__('page.post'));
    }
}
