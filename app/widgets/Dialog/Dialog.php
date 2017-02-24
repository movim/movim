<?php

use Movim\RPC;

class Dialog extends \Movim\Widget\Base
{
    function load()
    {
        $this->addjs('dialog.js');
    }

    static function fill($html = '', $scroll = false)
    {
        RPC::call('MovimTpl.fill', '#dialog', $html);

        if($scroll) {
            RPC::call('Dialog.addScroll');
        }
    }

    public function ajaxClear()
    {
        RPC::call('MovimUtils.removeClass', '#dialog', 'scroll');
        RPC::call('MovimTpl.fill', '#dialog', '');
    }

    function display()
    {
    }
}
