<?php

use Movim\Controller\Base;

class AboutController extends Base
{
    public function dispatch()
    {
        $this->page->setTitle(__('page.about'));
    }
}
