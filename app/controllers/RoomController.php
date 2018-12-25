<?php

use Movim\Controller\Base;

class RoomController extends Base
{
    public function load()
    {
    }

    public function dispatch()
    {
        $this->page->setTitle(__('page.room'));
    }
}
