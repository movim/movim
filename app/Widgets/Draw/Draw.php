<?php

namespace App\Widgets\Draw;

use Movim\Widget\Base;

class Draw extends Base
{
    public function load()
    {
        $this->addjs('draw.js');
        $this->addcss('draw.css');
    }

    public function ajaxHttpClear()
    {
        $this->rpc('MovimTpl.fill', '#draw', '');
    }

    public function ajaxHttpGet()
    {
        $view = $this->tpl();
        $this->rpc('MovimTpl.fill', '#draw', $view->draw('_draw'));
        $this->rpc('Draw.init');
    }
}
