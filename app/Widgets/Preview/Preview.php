<?php

namespace App\Widgets\Preview;

use App\Widgets\Toast\Toast;
use Movim\Widget\Base;

use Respect\Validation\Validator;

class Preview extends Base
{
    public function load()
    {
        $this->addcss('preview.css');
        $this->addjs('preview.js');
    }

    public function ajaxHttpShow(string $url, ?string $messageId = null)
    {
        if (!Validator::url($url)->isValid($url)) {
            return;
        }

        $view = $this->tpl();

        try {
            $view->assign('embed', (new \App\Url)->resolve($url));
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }

        $view->assign('url', $url);
        $view->assign('messageid', $messageId);

        $this->rpc('Preview.fill', $view->draw('_preview'));
    }

    public function ajaxHttpGallery(string $url, $number = 0)
    {
        if (!Validator::url($url)->isValid($url)) {
            return;
        }

        $view = $this->tpl();
        try {
            $view->assign('embed', (new \App\Url)->resolve($url));
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }
        $view->assign('imagenumber', $number);

        $this->rpc('MovimTpl.fill', '#preview', $view->draw('_preview_gallery'));
    }

    public function ajaxHttpHide()
    {
        $this->rpc('MovimTpl.fill', '#preview', '');
    }

    public function ajaxCopyNotify()
    {
        Toast::send($this->__('preview.link_copied'));
    }
}
