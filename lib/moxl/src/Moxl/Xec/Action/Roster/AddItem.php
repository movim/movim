<?php

namespace Moxl\Xec\Action\Roster;

use Moxl\Xec\Action;
use Moxl\Stanza\Roster;
use App\Roster as DBRoster;
use App\User as DBUser;

class AddItem extends Action
{
    private $_to;
    private $_name;
    private $_group;

    public function request()
    {
        $this->store();
        Roster::add($this->_to, $this->_name, $this->_group);
    }

    public function setTo($to)
    {
        $this->_to = $to;
        return $this;
    }

    public function setName($name)
    {
        $this->_name = $name;
        return $this;
    }

    public function setGroup($group)
    {
        $this->_group = $group;
        return $this;
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
