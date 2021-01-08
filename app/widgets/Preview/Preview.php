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
        if (!Validator::url($url)->validate($url)) {
            return;
        }

        $view = $this->tpl();
        $view->assign('url', $url);

        $this->rpc('MovimTpl.fill', '#preview', $view->draw('_preview'));
    }

    public function ajaxGallery($url, $number = 0)
    {
        if (!Validator::url($url)->validate($url)) {
            return;
        }

        $view = $this->tpl();
        $view->assign('embed', (new \App\Url)->resolve($url));
        $view->assign('imagenumber', $number);

        $this->rpc('MovimTpl.fill', '#preview', $view->draw('_preview_gallery'));
    }

    public function ajaxHide()
    {
        $this->rpc('MovimTpl.fill', '#preview', '');
    }
}
