<?php

namespace Moxl\Xec\Payload;

use App\Contact;

class Location extends Payload
{
    public function handle($stanza, $parent = false)
    {
        $from = current(explode('/', (string)$parent->attributes()->from));

        if (isset($stanza->items->item->geoloc)) {
            $contact = Contact::firstOrNew(['id' => $from]);
            $contact->setLocation($stanza->items->item);
            $contact->save();

            if ($from == \App\User::me()->id) {
                $this->event('mylocation');
                $this->event('mypresence');
            } else {
                $this->pack($contact);
                $this->deliver();
            }
        }
    }
}
