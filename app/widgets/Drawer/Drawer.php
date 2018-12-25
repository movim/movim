<?php

use Movim\Widget\Base;
use Movim\RPC;

class Drawer extends Base
{
    public function load()
    {
        $this->addjs('drawer.js');
        $this->addcss('drawer.css');
    }

    public static function fill($html = '', $actions = false)
    {
        RPC::call('MovimTpl.fill', '#drawer', $html);
        RPC::call('MovimTpl.hideMenu');
        RPC::call('MovimUtils.removeClass', '#drawer', 'empty');
        if ($actions) {
            RPC::call('MovimUtils.addClass', '#drawer', 'actions');
        }
    }

    public function ajaxClear()
    {
        RPC::call('MovimUtils.addClass', '#drawer', 'empty');
        RPC::call('MovimUtils.removeClass', '#drawer', 'actions');
    }
}
