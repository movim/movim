<?php

namespace Moxl\Xec\Action\Nickname;

use Moxl\Xec\Action;
use Moxl\Stanza\Nickname;

class Set extends Action
{
    private $_nickname;

    public function request()
    {
        $this->store();
        Nickname::set($this->_nickname);
    }

    public function setNickname($nickname)
    {
        $this->_nickname = $nickname;
        return $this;
    }

    public function handle($stanza, $parent = false) {
    }
}
