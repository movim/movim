<?php

namespace Moxl\Xec\Action\Bookmark2;

use Moxl\Xec\Action;
use Moxl\Stanza\Pubsub;

class Delete extends Action
{
    protected $_id;
    protected $_version = '1';

    public function request()
    {
        $this->store();
        Pubsub::postDelete(false, 'urn:xmpp:bookmarks:'.$this->_version, $this->_id);
    }

    public function handle($stanza, $parent = false)
    {
        \App\User::me()->session->conferences()->where('conference', $this->_id)->delete();
        $this->deliver();
    }
}