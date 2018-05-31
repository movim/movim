<?php

namespace Moxl\Xec\Action\Vcard;

use Moxl\Xec\Action;
use Moxl\Stanza\Vcard;

class Get extends Action
{
    protected $_to;

    public function request()
    {
        $this->store();
        Vcard::get($this->_to);
    }

    public function handle($stanza, $parent = false)
    {
        $contact = \App\Contact::firstOrNew(['id' => $this->_to]);
        $contact->set($stanza, $this->_to);
        $contact->createThumbnails();
        $contact->save();

        $this->pack($contact);
        $this->deliver();
    }
}
