<?php

namespace Moxl\Xec\Payload;

class Displayed extends Payload
{
    public function handle($stanza, $parent = false)
    {
        // Handle only MUC messages with a proper stanza-id
        $message = ('groupchat' == (string)$parent->attributes()->type)
            ? \App\User::me()->messages()
                    ->where('stanzaid', (string)$stanza->attributes()->id)
                    ->where('jidfrom', current(explode('/',
                        (string)$parent->attributes()->from
                    )))
                    ->first()
            : \App\User::me()->messages()
                    ->where('originid', (string)$stanza->attributes()->id)
                    ->where('jidfrom', current(explode('/',
                        (string)$parent->attributes()->to
                    )))
                    ->first();

        if ($message && $message->displayed == null) {
            $message->displayed = gmdate('Y-m-d H:i:s');
            $message->seen = true;
            $message->save();

            $this->pack($message);
            $this->deliver();
        }
    }
}
