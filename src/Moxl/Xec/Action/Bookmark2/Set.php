<?php

namespace Moxl\Xec\Action\Bookmark2;

use Moxl\Xec\Action;
use Moxl\Stanza\Bookmark2;
use App\Conference;
use Moxl\Xec\Action\Pubsub\SetConfig;

class Set extends Action
{
    protected $_conference;
    protected $_version = '1';
    protected bool $_withPublishOption = true;

    public function request()
    {
        $this->store();
        Bookmark2::set($this->_conference, $this->_version, $this->_withPublishOption);
    }

    public function setConference(Conference $conference)
    {
        $this->_conference = $conference;
        return $this;
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        me()->session->conferences()
            ->where('conference', $this->_conference->conference)
            ->delete();

        $conference = new Conference;
        $conference->conference = $this->_conference->conference;
        $conference->name = $this->_conference->name;
        $conference->autojoin = $this->_conference->autojoin;
        $conference->pinned = $this->_conference->pinned;
        $conference->nick = $this->_conference->nick;
        $conference->notify = $this->_conference->notify;
        $conference->save();

        $this->pack($conference);
        $this->deliver();
    }

    public function errorPreconditionNotMet(string $errorId, ?string $message = null)
    {
        $this->errorConflict($errorId, $message);
    }

    public function errorResourceConstraint(string $errorId, ?string $message = null)
    {
        $this->errorConflict($errorId, $message);
    }

    public function errorConflict(string $errorId, ?string $message = null)
    {
        $config = new SetConfig;
        $config->setNode(Bookmark2::$node.$this->_version)
               ->setData(Bookmark2::$nodeConfig)
               ->request();

        $this->_withPublishOption = false;
        $this->request();
    }
}
