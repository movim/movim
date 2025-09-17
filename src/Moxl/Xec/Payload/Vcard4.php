<?php

namespace Moxl\Xec\Payload;

use App\Contact;

class Vcard4 extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $from = baseJid((string)$parent->attributes()->from);

        $contact = Contact::firstOrNew(['id' => $from]);
        $contact->setVcard4($stanza->items->item->vcard);
        $contact->save();

        $this->pack($contact->id);
        $this->event('vcard');
    }
}
