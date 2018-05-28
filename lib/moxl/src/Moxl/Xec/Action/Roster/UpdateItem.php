<?php

namespace Moxl\Xec\Action\Roster;

use Moxl\Xec\Action;
use Moxl\Stanza\Roster;

class UpdateItem extends Action
{
    private $_to;
    private $_from;
    private $_name;
    private $_group;

    public function request()
    {
        $this->store();
        Roster::update($this->_to, $this->_name, $this->_group);
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
        $roster = \App\Roster::firstOrNew(['jid' => $this->_to]);
        $roster->name = $this->_name;
        $roster->group = $this->_group;
        $roster->save();

        $this->pack($this->_to);
        $this->deliver();
    }
}
