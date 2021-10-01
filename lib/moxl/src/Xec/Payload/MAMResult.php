<?php

namespace Moxl\Xec\Payload;

use Movim\Session;
//use App\MessageBuffer;

class MAMResult extends Payload
{
    public function handle($stanza, $parent = false)
    {
        $to = current(explode('/', (string)$parent->attributes()->to));
        $session = Session::start();

        if ($stanza->forwarded->delay
        && isset($stanza->attributes()->queryid)
        && $session->get('mamid'.(string)$stanza->attributes()->queryid) == true) {
            /**
             * Optimisation: Force the message to be only instanciated, without requesting
             * the database because the MessageBuffer bellow will take care of that
             */
            $message = \App\Message::findByStanza($stanza->forwarded->message/*, true*/);
            $message = $message->set($stanza->forwarded->message, $stanza->forwarded);

            if ($message->type == 'groupchat') {
                $message->jidfrom = current(explode('/', ($message->jidfrom)));
            }

            if (!empty($to) && empty($message->jidto)) {
                $message->jidto = $to;
            }

            if (!$message->encrypted
            && $message->valid()
            && (!$message->isEmpty() || $message->isSubject())) {
                // Set the "old" message as seen
                if (\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $message->published)->addWeek()->isBefore(\Carbon\Carbon::now())) {
                    $message->seen = true;
                }

                //MessageBuffer::getInstance()->append($message, function() use ($message) {
                    $message->save();
                    $message->clearUnreads();

                    $this->pack($message);
                    $this->deliver();
                //});
            }
        }
    }
}
