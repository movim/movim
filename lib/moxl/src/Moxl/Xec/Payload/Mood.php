<?php

namespace Moxl\Xec\Payload;

use App\Contact;

class Mood extends Payload
{
    public function handle($stanza, $parent = false)
    {
        $from = current(explode('/', (string)$parent->attributes()->from));

        if (isset($stanza->items->item->mood)
        && $stanza->items->item->mood->count() > 0) {
            $arrmood = [];
            foreach ($stanza->items->item->mood->children() as $mood) {
                if ($mood->getName() != 'text') {
                    array_push($arrmood, $mood->getName());
                }
            }

            if (count($arrmood) > 0) {
                $contact = Contact::firstOrNew(['id' => $from]);
                $contact->mood = serialize($arrmood);
                $contact->save();
            }
        }
    }
}
