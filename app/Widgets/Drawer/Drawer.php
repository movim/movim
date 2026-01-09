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

    public function fill(string $key, $html = '', bool $actions = false, bool $tiny = false)
    {
        (new RPC(user: $this->me, sessionId: $this->sessionId))->call('MovimTpl.fill', '#drawer', $html);
        (new RPC(user: $this->me, sessionId: $this->sessionId))->call('MovimUtils.removeClass', 'body > nav', 'active');

        if ($actions) {
            (new RPC(user: $this->me, sessionId: $this->sessionId))->call('MovimUtils.addClass', '#drawer', 'actions');
        }

        if ($tiny) {
            (new RPC(user: $this->me, sessionId: $this->sessionId))->call('MovimUtils.addClass', '#drawer', 'tiny');
        }

        (new RPC(user: $this->me, sessionId: $this->sessionId))->call('Drawer.open', $key);
    }

    public function ajaxClear()
    {
        (new RPC(user: $this->me, sessionId: $this->sessionId))->call('MovimTpl.fill', '#drawer', '');
        (new RPC(user: $this->me, sessionId: $this->sessionId))->call('MovimUtils.removeClass', '#drawer', 'actions');
        (new RPC(user: $this->me, sessionId: $this->sessionId))->call('MovimUtils.removeClass', '#drawer', 'tiny');
    }
}
