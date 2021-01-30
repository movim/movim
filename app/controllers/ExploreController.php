<?php

use Movim\Controller\Base;

class ExploreController extends Base
{
    public function load()
    {
        $this->session_only = true;
    }

    public function dispatch()
    {
        if (!empty($this->fetchGet('s') && $this->fetchGet('s') == 'servers')) {
            $this->page->setTitle(__('communities.servers'));
        } else {
            $this->page->setTitle(__('page.explore'));
        }
    }
}
