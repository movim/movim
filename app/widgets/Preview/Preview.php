<?php

use Movim\Widget\Base;

use Respect\Validation\Validator;

class Preview extends Base
{
    public function load()
    {
        $this->addcss('preview.css');
        $this->addjs('preview.js');
    }

    public function ajaxShow($url)
    {
        if (!Validator::url($url)->validate($url)) return;

        $view = $this->tpl();
        $view->assign('url', $url);

        $this->rpc('MovimTpl.fill', '#preview', $view->draw('_preview'));
    }

    public function ajaxHide()
    {
        $this->rpc('MovimTpl.fill', '#preview', '');
    }
}
