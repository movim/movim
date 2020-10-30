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
        $contact = \App\Contact::firstOrNew(['id' => $this->_to]);

        /*
         * Several sessions on the instance can ask simultaneously for a refresh.
         * Because only one is needed we add a state in the DB after the first one.
         * The state is reset when the answer is received
         */
        if ($contact && $contact->avatarrequested) {
            return false;
        } else {
            $contact->avatarrequested = true;
            $contact->save();
        }

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
         * Specific case if the returned stanza didn't contained a hash received trough a
         * presence or is different, we save it then we don't request it each time
         */
        if  ($this->_avatarhash
        && (
            empty($contact->avatarhash)
            || $contact->avatarhash != $this->_avatarhash)
        ) {
            $contact->avatarhash = $this->_avatarhash;
            $notify = false;
        }

        $contact->avatarrequested = false;
        $contact->save();

        if ($notify) {
            $this->pack($contact);
            $this->deliver();
        }
    }

    public function error($error)
    {
        $contact = \App\Contact::firstOrNew(['id' => $this->_to]);
        $contact->avatarrequested = false;
        $contact->avatarhash = $this->_avatarhash;
        $contact->save();
    }
}
