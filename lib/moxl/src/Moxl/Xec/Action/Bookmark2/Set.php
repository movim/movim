<?php

namespace Moxl\Xec\Action\Bookmark2;

use App\Conference;
use Moxl\Xec\Action;
use Moxl\Stanza\Bookmark2;

class Set extends Action
{
    protected $_conference;

    public function request()
    {
        $this->store();
        Bookmark2::set($this->_conference);
    }

    public function setConference(Conference $conference)
    {
        $this->_conference = $conference;
        return $this;
    }

    public function handle($stanza, $parent = false)
    {
        $this->_conference->save();
        $this->deliver();
    }
}
