<?php

namespace Moxl\Xec\Payload;

class Carbons extends Payload
{
    public function handle($stanza, $parent = false)
    {
        $parentfrom = current(explode('/', (string)$parent->attributes()->from));

        $message = $stanza->forwarded->message;

        $from = current(explode('/',(string)$message->attributes()->from));
        $to = current(explode('/',(string)$message->attributes()->to));

        $user = new \User;
        if($parentfrom == $user->getLogin()) {
            if($message->composing)
                $this->event('composing', [$from, $to]);
            if($message->paused)
                $this->event('paused', [$from, $to]);
            if($message->gone)
                $this->event('gone', [$from, $to]);

            if($message->body || $message->subject) {
                $m = new \Modl\Message;
                $m->set($message, $stanza->forwarded);

                if(!preg_match('#^\?OTR#', $m->body)) {
                    $md = new \Modl\MessageDAO;
                    $md->set($m);

                    $this->pack($m);
                    $this->deliver();
                }
            }
        }
    }
}
