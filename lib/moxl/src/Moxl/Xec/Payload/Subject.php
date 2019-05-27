<?php

namespace Moxl\Xec\Payload;

class Subject extends Payload
{
    public function handle($stanza, $parent = false)
    {
        $jid = explodeJid((string)$parent->attributes()->from);
        $to = explodeJid((string)$parent->attributes()->to)['jid'];

        if ($parent->subject) {
            $message = new \App\Message;

            $message->user_id    = $to;
            $message->jidto      = $to;
            $message->jidfrom    = $jid['jid'];

            if ($jid['resource']) {
                $message->resource = $jid['resource'];
            }

            $message->type    = (string)$parent->attributes()->type;
            $message->body    = (string)$parent->body;
            $message->subject = (string)$parent->subject;

            $message->published = ($parent->delay)
                ? gmdate('Y-m-d H:i:s', strtotime($parent->delay->attributes()->stamp))
                : gmdate('Y-m-d H:i:s');
            $message->delivered = date('Y-m-d H:i:s');

            $message->save();

            $this->pack($message);
            $this->deliver();
        }
    }
}
