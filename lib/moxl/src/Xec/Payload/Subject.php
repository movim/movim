<?php

namespace Moxl\Xec\Payload;

class Subject extends Payload
{
    public function handle($stanza, $parent = false)
    {
        $jid = explode('/', (string)$parent->attributes()->from);
        $to = current(explode('/', (string)$parent->attributes()->to));

        if ($parent->subject) {
            $message = new \App\Message;

            $message->user_id    = $to;
            $message->jidto      = $to;
            $message->jidfrom    = $jid[0];

            if (isset($jid[1])) {
                $message->resource = $jid[1];
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
