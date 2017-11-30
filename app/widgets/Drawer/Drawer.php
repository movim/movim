<?php

use Movim\RPC;

class Drawer extends \Movim\Widget\Base
{
    function load()
    {
        $this->addjs('drawer.js');
    }

    static function fill($html = '', $actions = false)
    {
        RPC::call('MovimTpl.fill', '#drawer', $html);
        RPC::call('MovimTpl.hideMenu');
        RPC::call('MovimUtils.removeClass', '#drawer', 'empty');
        if($actions) {
            RPC::call('MovimUtils.addClass', '#drawer', 'actions');
        }
    }

    public function ajaxClear()
    {
        RPC::call('MovimUtils.addClass', '#drawer', 'empty');
        RPC::call('MovimUtils.removeClass', '#drawer', 'actions');
    }
}
