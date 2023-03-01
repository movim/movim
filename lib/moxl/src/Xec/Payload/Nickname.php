<?php

namespace Moxl\Xec\Payload;

use App\Contact;

class Nickname extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $from = baseJid((string)$parent->attributes()->from);

        if ($stanza->items->item->nick) {
            $contact = Contact::firstOrNew(['id' => $from]);
            $contact->nickname = (string)$stanza->items->item->nick;
            $contact->save();
        }
    }
}
