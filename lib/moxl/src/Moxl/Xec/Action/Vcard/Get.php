<?php

namespace Moxl\Xec\Action\Vcard;

use Moxl\Xec\Action;
use Moxl\Stanza\Vcard;

class Get extends Action
{
    protected $_to;
    protected $_avatarhash;

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

        $notify = true;

        /**
         * Specific case if the returned stanza didn't contained a hash
         * received trough a presence, we save it then we don't request
         * it each time
         */
        if  ($this->_avatarhash && !$contact->avatarhash) {
            $contact->avatarhash = $this->_avatarhash;
            $notify = false;
        }

        $contact->save();

        if ($notify) {
            $this->pack($contact);
            $this->deliver();
        }
    }

    public function error($error)
    {
        $contact = \App\Contact::firstOrNew(['id' => $this->_to]);
        $contact->avatarhash = $this->_avatarhash;
        $contact->save();
    }
}
