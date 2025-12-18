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
        $this->iq(MAM::setConfig($this->_default), type: 'set');
    }

    public function setDefault($default)
    {
        $this->_default = (in_array($default, ['always', 'never', 'roster']))
            ? $default
            : 'roster';

        return $this;
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $this->deliver();
    }
}
