<?php


namespace Moxl\Xec\Payload;

class ReceiptAck extends Payload
{
    public function handle($stanza, $parent = false)
    {
        $from = (string)$parent->attributes()->from;
        $id = (string)$stanza->attributes()->id;

        $message = \App\User::me()->messages
                                  ->where('id', (string)$stanza->attributes()->id)
                                  ->first();

        if($message) {
            $message->delivered = gmdate('Y-m-d H:i:s');
            $message->save();

            $this->pack($message);
            $this->deliver();
        }
    }
}
