<?php

namespace App\Widgets\Drawer;

use Movim\Widget\Base;
use Movim\RPC;

class Drawer extends Base
{
    public function load()
    {
        $this->addjs('drawer.js');
        $this->addcss('drawer.css');
    }

    public static function fill(string $key, $html = '', bool $actions = false, bool $tiny = false)
    {
        RPC::call('MovimTpl.fill', '#drawer', $html);
        RPC::call('MovimUtils.removeClass', 'body > nav', 'active');

        if ($actions) {
            RPC::call('MovimUtils.addClass', '#drawer', 'actions');
        }

        if ($tiny) {
            RPC::call('MovimUtils.addClass', '#drawer', 'tiny');
        }

        RPC::call('Drawer.open', $key);
    }

    public function ajaxClear()
    {
        RPC::call('MovimTpl.fill', '#drawer', '');
        RPC::call('MovimUtils.removeClass', '#drawer', 'actions');
        RPC::call('MovimUtils.removeClass', '#drawer', 'tiny');
    }
}
