<?php

namespace Moxl\Xec\Action\Roster;

use Moxl\Xec\Action;
use Moxl\Stanza\Roster;
use App\Roster as DBRoster;

class AddItem extends Action
{
    protected $_to;
    protected $_name;
    protected $_group;

    public function request()
    {
        $this->store();
        Roster::add($this->_to, $this->_name, $this->_group);
    }

    public function handle($stanza, $parent = false)
    {
        $roster = DBRoster::firstOrNew(['jid' => $this->_to]);
        $roster->group = $this->_group;
        $roster->name = $this->_name;
        $roster->save();

        $this->pack($this->_to);
        $this->deliver();
    }
}
