<?php

namespace Moxl\Xec\Action\MAM;

use Moxl\Xec\Action;
use Moxl\Stanza\MAM;

class SetConfig extends Action
{
    private $_default;

    public function request()
    {
        $this->store();
        MAM::setConfig($this->_default);
    }

    public function setDefault($default)
    {
        $this->_default = (in_array($default, ['always', 'never', 'roster']))
            ? $default
            : 'roster';

        return $this;
    }

    public function handle($stanza, $parent = false)
    {
        $this->deliver();
    }
}
