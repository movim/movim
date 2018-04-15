<?php

namespace Moxl\Xec\Payload;

class Displayed extends Payload
{
    public function handle($stanza, $parent = false)
    {
        $id = (string)$stanza->attributes()->id;

        $message = \App\User::me()->messages
                                  ->where('id', (string)$stanza->attributes()->id)
                                  ->first();

        if($message) {
            $message->displayed = gmdate('Y-m-d H:i:s');
            $message->save();

            $this->pack($message);
            $this->deliver();
        }
    }
}
