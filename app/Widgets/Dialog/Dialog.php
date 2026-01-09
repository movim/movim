<?php

namespace App\Widgets\Dialog;

use Movim\Widget\Base;
use Movim\RPC;

class Dialog extends Base
{
    public function load()
    {
        $this->addjs('dialog.js');
    }

    public function fill($html = '', $scroll = false, $locked = false)
    {
        (new RPC(user: $this->me, sessionId: $this->sessionId))->call('MovimTpl.fill', '#dialog', $html);

        if ($scroll) {
            (new RPC(user: $this->me, sessionId: $this->sessionId))->call('Dialog.addScroll');
        }

        if ($locked) {
            (new RPC(user: $this->me, sessionId: $this->sessionId))->call('Dialog.addLocked');
        }
    }

    public function ajaxClear()
    {
        (new RPC(user: $this->me, sessionId: $this->sessionId))->call('MovimUtils.removeClass', '#dialog', 'scroll');
        (new RPC(user: $this->me, sessionId: $this->sessionId))->call('MovimTpl.fill', '#dialog', '');
    }
}
