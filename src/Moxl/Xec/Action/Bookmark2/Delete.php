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
        $this->iq(Pubsub::itemDelete(false, 'urn:xmpp:bookmarks:'.$this->_version, $this->_id), type: 'set');
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $this->me->session->conferences()->where('conference', $this->_id)->delete();
        $this->deliver();
    }
}
