<?php

namespace Moxl\Xec\Payload;

use App\Contact;

class Location extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $from = baseJid((string)$parent->attributes()->from);

        if (isset($stanza->items->item->geoloc)) {
            $contact = Contact::firstOrNew(['id' => $from]);
            $contact->setLocation($stanza->items->item);
            $contact->save();

            if ($from == me()->id) {
                $this->event('mylocation');
                $this->event('mypresence');
            } else {
                $this->pack($contact);
                $this->deliver();
            }
        }
    }
}
