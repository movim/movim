<?php

namespace Moxl\Xec\Payload;

class ReceiptRequest extends Payload
{
    public function handle($stanza, $parent = false)
    {
        $from = (string)$parent->attributes()->from;

        $id = ($parent->{'origin-id'}
            && $parent->{'origin-id'}->attributes()->xmlns == 'urn:xmpp:sid:0')
            ? (string)$parent->{'origin-id'}->attributes()->id
            : (string)$parent->attributes()->id;

        \Moxl\Stanza\Message::receipt($from, $id);

        $message = \App\User::me()->messages()
                                  ->where('originid', $id)
                                  ->where('jidfrom', current(explode('/', $from)))
                                  ->first();

        if ($message) {
            $message->delivered = gmdate('Y-m-d H:i:s');
            $message->save();
        }
    }
}
