<?php

use Respect\Validation\Validator;

class Preview extends \Movim\Widget\Base
{
    function load()
    {
        $this->addcss('preview.css');
        $this->addjs('preview.js');
    }

    public function ajaxShow($url)
    {
        if(!Validator::url($url)->validate($url)) return;

        $view = $this->tpl();
        $view->assign('url', $url);

        $this->rpc('MovimTpl.fill', '#preview', $view->draw('_preview', true));
    }

    public function ajaxHide()
    {
        $this->rpc('MovimTpl.fill', '#preview', '');
    }
}
