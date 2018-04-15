<?php

namespace Moxl\Xec\Payload;

use Movim\User;

class Carbons extends Payload
{
    public function handle($stanza, $parent = false)
    {
        $parentfrom = current(explode('/', (string)$parent->attributes()->from));

        $message = $stanza->forwarded->message;

        $from = current(explode('/',(string)$message->attributes()->from));
        $to = current(explode('/',(string)$message->attributes()->to));

        if($parentfrom == \App\User::me()->id) {
            if ($message->composing)
                $this->event('composing', [$from, $to]);
            if ($message->paused)
                $this->event('paused', [$from, $to]);
            if ($message->gone)
                $this->event('gone', [$from, $to]);

            if ($message->body || $message->subject) {
                $m = \App\Message::findByStanza($message);
                $m->set($message, $stanza->forwarded);

                if (!$m->isOTR()) {
                    $m->save();
                    $this->pack($m);
                    $this->deliver();
                }
            }
        }
    }
}
