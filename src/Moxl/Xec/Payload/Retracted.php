<?php

namespace Moxl\Xec\Payload;

class Retracted extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $idKey = ($stanza->moderated
            && in_array(
                $stanza->moderated->attributes()->xmlns,
                [
                    'urn:xmpp:message-moderate:0', // buggy ejabberd implementation
                    'urn:xmpp:message-moderate:1'
                ]
            )) || $parent->attributes()->type == 'groupchat'
            ? 'stanzaid'
            : 'originid';

        $message = $this->me->messages()
            ->where($idKey, (string)$stanza->attributes()->id)
            ->where('jidfrom', bareJid((string)$parent->attributes()->from))
            ->first();

        if ($message) {
            $message->retract();
            $message->save();

            $this->pack($message);
            $this->deliver();
        }
    }
}
