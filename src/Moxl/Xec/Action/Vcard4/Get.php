<?php

namespace Moxl\Xec\Action\Vcard4;

use Moxl\Xec\Action;
use Moxl\Stanza\Vcard4;

class Get extends Action
{
    protected $_to;

    public function request()
    {
        $this->store();
        Vcard4::get($this->_to);
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        if ($stanza->pubsub->items->item) {
            $contact = \App\Contact::firstOrNew(['id' => $this->_to]);
            $contact->setVcard4($stanza->pubsub->items->item->vcard);
            $contact->save();

            $this->pack($contact->id);
            $this->deliver();
        } else {
            $this->error(false);
        }
    }

    public function error(string $errorId, ?string $message = null)
    {
        $r = new \Moxl\Xec\Action\Vcard\Get;
        $r->setTo($this->_to)->request();
    }
}
