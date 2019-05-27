<?php


namespace Moxl\Xec\Payload;

class ReceiptRequest extends Payload
{
    public function handle($stanza, $parent = false)
    {
        $from = (string)$parent->attributes()->from;
        $id = (string)$parent->attributes()->id;

        \Moxl\Stanza\Message::receipt($from, $id);

        $message = \App\User::me()->messages()
                                  ->where('id', $id)
                                  ->where('jidfrom', explodeJid($from)['jid'])
                                  ->first();

        if ($message) {
            $message->delivered = gmdate('Y-m-d H:i:s');
            $message->save();
        }
    }
}
