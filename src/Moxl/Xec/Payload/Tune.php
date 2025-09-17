<?php

namespace Moxl\Xec\Payload;

use App\Contact;

class Tune extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $from = baseJid((string)$parent->attributes()->from);

        if (isset($stanza->items->item->tune)
        && $stanza->items->item->tune->count() > 0) {
            $contact = Contact::firstOrNew(['id' => $from]);
            $contact->setTune($stanza);
            $contact->save();

            $this->pack($from);
            $this->event('tune');
        }
    }
}
