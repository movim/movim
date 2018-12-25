<?php

use Movim\Controller\Base;

class AboutController extends Base
{
    public function load()
    {
        $this->session_only = false;
    }

    public function dispatch()
    {
        $this->page->setTitle(__('page.about'));
    }
}
