<?php

use Movim\Widget\Base;

class Dictaphone extends Base
{
    public function load()
    {
        $this->addjs('dictaphone.js');
        $this->addcss('dictaphone.css');
    }

    public function ajaxHttpGet()
    {
        if ($this->getUser()->hasUpload()) {
            $view = $this->tpl();
            $this->rpc('MovimTpl.fill', '#dictaphone', $view->draw('_dictaphone'));
            $this->rpc('Dictaphone.init');
        }
    }
}
