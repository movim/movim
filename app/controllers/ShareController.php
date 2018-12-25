<?php

use Movim\Controller\Base;

class ShareController extends Base
{
    public function load()
    {
        $this->session_only = true;
    }

    public function dispatch()
    {
        $this->page->setTitle(__('page.share'));
    }
}
