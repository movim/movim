<?php
use Movim\Controller\Base;

class RoomController extends Base
{
    function load() {
    }

    function dispatch() {
        $this->page->setTitle(__('page.room'));
    }
}
