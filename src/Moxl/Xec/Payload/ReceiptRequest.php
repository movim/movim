<?php

namespace Moxl\Xec\Payload;

use Moxl\Stanza\Message;

class ReceiptRequest extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $from = (string)$parent->attributes()->from;

        $id = ($parent->{'origin-id'}
            && $parent->{'origin-id'}->attributes()->xmlns == 'urn:xmpp:sid:0')
            ? (string)$parent->{'origin-id'}->attributes()->id
            : (string)$parent->attributes()->id;

        $this->send(Message::maker(
            to: bareJid($from),
            id: $id,
            type: (string)$parent->attributes()->type,
            messageId: generateUUID(),
            receipts: 'received'
        ));

        $message = $this->me->messages()
            ->where('originid', $id)
            ->where('jidfrom', bareJid($from))
            ->first();

        if ($message && $message->delivered == null) {
            $message->delivered = gmdate('Y-m-d H:i:s');
            $message->save();
        }
    }
}
