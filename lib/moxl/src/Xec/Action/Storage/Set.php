<?php

namespace Moxl\Xec\Action\Storage;

use Moxl\Xec\Action;
use Moxl\Stanza\Storage;

class Set extends Action
{
    private $_xmlns = 'movim:prefs';
    protected $_data;

    public function request()
    {
        $this->store();
        Storage::publish($this->_xmlns, $this->_data);
    }

    public function handle($stanza, $parent = false)
    {
        $this->pack(unserialize($this->_data));
        $this->deliver();
    }
}
