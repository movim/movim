<?php

namespace Moxl\Xec\Action\Bookmark2;

use Moxl\Xec\Action;
use Moxl\Stanza\Bookmark2;
use App\Conference;

class Set extends Action
{
    protected $_conference;
    protected $_version = '1';

    public function request()
    {
        $this->store();

        // If we set the version 1, ensure to remove 0
        if ($this->_version == '1' && $this->_conference->bookmarkversion == 0) {
            $d = new Delete;
            $d->setId($this->_conference->conference)
              ->setVersion('0')
              ->request();
        }

        Bookmark2::set($this->_conference, $this->_version);
    }

    public function setConference(Conference $conference)
    {
        $this->_conference = $conference;
        return $this;
    }

    public function handle($stanza, $parent = false)
    {
        $this->_conference->save();
        $this->pack($this->_conference);
        $this->deliver();
    }
}
