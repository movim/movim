<?php

namespace Moxl\Xec\Action\Roster;

use Moxl\Xec\Action;
use Moxl\Stanza\Roster;

class UpdateItem extends Action
{
    protected $_to;
    protected $_from;
    protected $_name;
    protected $_group;

    public function request()
    {
        $this->store();
        Roster::update($this->_to, $this->_name, $this->_group);
    }

    public function handle($stanza, $parent = false)
    {
        $roster = \App\Roster::firstOrNew(['jid' => $this->_to]);
        $roster->name = $this->_name;
        $roster->group = $this->_group;
        $roster->save();

        $this->pack($this->_to);
        $this->deliver();
    }
}
