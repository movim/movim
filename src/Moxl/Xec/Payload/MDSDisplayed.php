<?php

namespace Moxl\Xec\Payload;

class MDSDisplayed extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        if ((string)$this->from != $this->me?->id) {
            return;
        }

        if (
            $parent->addresses?->attributes()->xmlns == 'http://jabber.org/protocol/address'
            && $this->me?->session
            && $parent->addresses->address->attributes()->jid == $this->me->id . '/' . $this->me->session->resource
        ) {
            return;
        }

        $message = $this->me->messages()
            ->where('stanzaid', (string)$stanza->items->item->displayed->{'stanza-id'}->attributes()->id)
            ->where('jidfrom', (string)$stanza->items->item->attributes()->id)
            ->first();

        if ($message && $message->displayed == null) {
            $message->displayed = gmdate('Y-m-d H:i:s');
            $message->save();

            if ($message->jidto == $message->user_id) {
                $this->me->messages()
                    ->where('jidfrom', $message->jidfrom)
                    ->where('seen', false)
                    ->update(['seen' => true]);
            }

            $this->pack($message);
            $this->event('displayed');
        }
    }
}
