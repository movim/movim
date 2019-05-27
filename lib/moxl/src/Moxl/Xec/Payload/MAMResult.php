<?php

namespace Moxl\Xec\Payload;

use Movim\User;
use Movim\Session;

class MAMResult extends Payload
{
    public function handle($stanza, $parent = false)
    {
        $to = explodeJid((string)$parent->attributes()->to)['jid'];
        $session = Session::start();

        if ($stanza->forwarded->delay
        && isset($stanza->attributes()->queryid)
        && $session->get('mamid'.(string)$stanza->attributes()->queryid) == true) {
            $message = \App\Message::findByStanza($stanza->forwarded->message);
            $message = $message->set($stanza->forwarded->message, $stanza->forwarded);

            if ($message->type == 'groupchat') {
                $message->jidfrom = explodeJid(($message->jidfrom)['jid']);
            }

            if (!empty($to) && empty($message->jidto)) {
                $message->jidto = $to;
            }

            if (!$message->isOTR()
            && (!$message->isEmpty() || $message->isSubject())) {
                $message->save();

                $this->pack($message);
                $this->deliver();
            }
        }
    }
}
