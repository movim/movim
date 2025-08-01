<?php

namespace Moxl\Xec\Payload;

class ReceiptRequest extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $from = (string)$parent->attributes()->from;

        $id = ($parent->{'origin-id'}
            && $parent->{'origin-id'}->attributes()->xmlns == 'urn:xmpp:sid:0')
            ? (string)$parent->{'origin-id'}->attributes()->id
            : (string)$parent->attributes()->id;

        \Moxl\Stanza\Message::received(baseJid($from), $id, (string)$parent->attributes()->type);

        $message = me()->messages()
                                  ->where('originid', $id)
                                  ->where('jidfrom', baseJid($from))
                                  ->first();

        if ($message && $message->delivered == null) {
            $message->delivered = gmdate('Y-m-d H:i:s');
            $message->save();
        }
    }
}
