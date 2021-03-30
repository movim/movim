<?php

use Movim\Widget\Base;

class PublishHelp extends Base
{
    public function load()
    {
    }

    public function ajaxDrawer()
    {
        $view = $this->tpl();
        Drawer::fill($view->draw('_publishhelp'), true);
    }

    public function prepareHelp()
    {
        $view = $this->tpl();
        return $view->draw('_publishhelp');
    }
}
