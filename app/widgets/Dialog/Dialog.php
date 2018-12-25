<?php

use Movim\Widget\Base;
use Movim\RPC;

class Dialog extends Base
{
    public function load()
    {
        $this->addjs('dialog.js');
    }

    public static function fill($html = '', $scroll = false)
    {
        RPC::call('MovimTpl.fill', '#dialog', $html);

        if ($scroll) {
            RPC::call('Dialog.addScroll');
        }
    }

    public function ajaxClear()
    {
        RPC::call('MovimUtils.removeClass', '#dialog', 'scroll');
        RPC::call('MovimTpl.fill', '#dialog', '');
    }
}
