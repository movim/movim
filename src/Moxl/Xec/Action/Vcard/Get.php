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

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $notify = true;

        $contact = \App\Contact::firstOrNew(['id' => $this->_to]);
        $contact->set($stanza);
        $contact->setAvatar($stanza);

        /**
         * Specific case if the returned stanza didn't contained a hash received trough a
         * presence or is different, we save it then we don't request it each time
         */
        /*if (
            $this->_avatarhash
            && (
                empty($contact->avatarhash)
                || $contact->avatarhash != $this->_avatarhash)
        ) {
            $contact->avatarhash = $this->_avatarhash;
            $notify = false;
        }*/

        $contact->save();

        if ($notify) {
            $this->pack($contact->id);
            $this->deliver();
        }
    }

    public function error(string $errorId, ?string $message = null)
    {
        $contact = \App\Contact::firstOrNew(['id' => $this->_to]);
        $contact->avatarhash = $this->_avatarhash;
        $contact->save();
    }
}
