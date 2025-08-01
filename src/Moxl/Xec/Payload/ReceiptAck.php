<?php

namespace Moxl\Xec\Payload;

class ReceiptAck extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        // Handle only MUC messages with a proper stanza-id
        $message = ('groupchat' == (string)$parent->attributes()->type)
            ? me()->messages()
                    ->where('stanzaid', (string)$stanza->attributes()->id)
                    ->where('jidfrom', current(explode('/',
                        (string)$parent->attributes()->from
                    )))
                    ->first()
            : me()->messages()
                    ->where('originid', (string)$stanza->attributes()->id)
                    ->where('jidfrom', current(explode('/',
                        (string)$parent->attributes()->to
                    )))
                    ->first();

        if ($message && $message->delivered == null) {
            $message->delivered = gmdate('Y-m-d H:i:s');
            $message->save();

            $this->pack($message);
            $this->deliver();
        }
    }
}
