<?php

namespace Movim\Template;

use Rain\Tpl;
use Movim\Widget\Base;

class Partial extends Tpl
{
    public function __construct(Base $widget)
    {
        $this->objectConfigure([
            'tpl_dir'       => APP_PATH.'widgets/'.$widget->getName().'/',
            'cache_dir'     => CACHE_PATH,
            'tpl_ext'       => 'tpl',
            'auto_escape'   => true
        ]);

        $this->assign('c', $widget);
    }

    public function draw($templateFilePath, $bool = true)
    {
        return parent::draw($templateFilePath, true);
    }
}
