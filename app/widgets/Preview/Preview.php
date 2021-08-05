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

    public function ajaxHttpShow($url)
    {
        if (!Validator::url($url)->validate($url)) {
            return;
        }

        $view = $this->tpl();

        try {
            $view->assign('embed', (new \App\Url)->resolve($url));
        } catch (Exception $e) {
            error_log($e->getMessage());
        }
        $view->assign('url', $url);

        $this->rpc('MovimTpl.fill', '#preview', $view->draw('_preview'));
    }

    public function ajaxHttpGallery($url, $number = 0)
    {
        if (!Validator::url($url)->validate($url)) {
            return;
        }

        $view = $this->tpl();
        try {
            $view->assign('embed', (new \App\Url)->resolve($url));
        } catch (Exception $e) {
            error_log($e->getMessage());
        }
        $view->assign('imagenumber', $number);

        $this->rpc('MovimTpl.fill', '#preview', $view->draw('_preview_gallery'));
    }

    public function ajaxHttpHide()
    {
        $this->rpc('MovimTpl.fill', '#preview', '');
    }
}
