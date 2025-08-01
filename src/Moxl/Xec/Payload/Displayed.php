<?php

namespace Moxl\Xec\Payload;

class Displayed extends Payload
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

        if ($message && $message->displayed == null) {
            $message->displayed = gmdate('Y-m-d H:i:s');
            $message->save();

            if ($message->jidto == $message->user_id) {
                me()->messages()
                    ->where('jidfrom', $message->jidfrom)
                    ->where('seen', false)
                    ->update(['seen' => true]);
            }

            $this->pack($message);
            $this->deliver();
        }
    }
}
