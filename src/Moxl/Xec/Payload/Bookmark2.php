<?php

namespace Moxl\Xec\Payload;

use App\Conference;

class Bookmark2 extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        if (
            bareJid((string)$parent->attributes()->from) != $this->me->id
            || (string)$parent->attributes()->from == (string)$parent->attributes()->to
        ) return;

        if ($stanza->items->retract) {
            $this->me->session
                ->conferences()
                ->where('conference', (string)$stanza->items->retract->attributes()->id)
                ->delete();

            $this->method('retract');
            $this->deliver();
        } else {
            $conference = new Conference;
            $conference->set($this->me->session, $stanza->items->item);

            $this->me->session->conferences()->where('conference', $conference->conference)->delete();

            $conference->save();

            $this->pack($conference);
            $this->deliver();
        }
    }
}
