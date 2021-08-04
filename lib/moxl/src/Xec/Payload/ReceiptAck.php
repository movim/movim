<?php

namespace Moxl\Xec\Payload;

class ReceiptAck extends Payload
{
    public function handle($stanza, $parent = false)
    {
        $message = \App\User::me()->messages()
                                  ->where('originid', (string)$stanza->attributes()->id)
                                  ->where('jidfrom', current(explode('/', (string)$parent->attributes()->to)))
                                  ->first();

        if ($message) {
            $message->delivered = gmdate('Y-m-d H:i:s');
            $message->save();

            $this->pack($message);
            $this->deliver();
        }
    }
}
