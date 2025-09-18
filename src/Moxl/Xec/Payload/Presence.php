<?php

namespace Moxl\Xec\Payload;

use App\Presence as DBPresence;
use App\PresenceBuffer;
use Movim\Session;
use Movim\ChatroomPings;
use Movim\CurrentCall;
use Moxl\Xec\Action\Presence\Muc;
use Moxl\Xec\Handler;

class Presence extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $jid = explodeJid($stanza->attributes()->from);

        if (me()->hasBlocked($jid['jid'])) {
            return;
        }

        if (
            (string)$stanza->attributes()->type === 'error'
            && isset($stanza->attributes()->id)
        ) {
            // Let's drop errors with an id, useless for us
        } else {
            $presence = DBPresence::findByStanza($stanza);
            $presence->set($stanza);

            if (CurrentCall::getInstance()->isStarted() && CurrentCall::getInstance()->mujiRoom == $jid['jid']) {
                $muji = me()->session->mujiCalls()
                    ->where('muc', $jid['jid'])
                    ->first();

                if ($muji) {
                    $this->pack($muji);
                    $this->method('muji_event');
                    $this->deliver();

                    $this->pack([$stanza, $presence], $presence->mucjid . '/' . $presence->mucjidresource);
                    $this->method('muji');
                    $this->deliver();
                }
            }

            PresenceBuffer::getInstance()->append($presence, function () use ($presence, $stanza, $jid) {
                if ((string)$stanza->attributes()->type == 'subscribe') {
                    $this->pack((string)$stanza->attributes()->from);
                    $this->event('subscribe');
                }

                if ($presence->muc) {
                    ChatroomPings::getInstance()->touch($presence->jid);

                    if ($presence->mucjid == me()->id) {
                        // Spectrum2 specific bug, we can receive two self-presences, one with several caps items
                        $cCount = 0;
                        foreach ($stanza->children() as $key => $content) {
                            if ($key == 'c') $cCount++;
                        }

                        if ($cCount > 1) {
                            $presence->delete();
                        }
                        // So we drop it

                        $session = Session::instance();

                        if ($presence->value != 5 && $presence->value != 6) {
                            $this->method('muc_handle');
                            $this->pack([$presence, false]);
                        }

                        /**
                         * Server bug case where we actually got an error from our resource but it didn't provide the
                         * id back in the stanza
                         */
                        elseif (
                            $session->get(Muc::$mucId . (string)$stanza->attributes()->from)
                            && !isset($stanza->attributes()->id)
                        ) {
                            /**
                             * Add back the id to the stanza and send it back to the stanza handler
                             */
                            $stanza->addAttribute('id', $session->get(Muc::$mucId . (string)$stanza->attributes()->from));
                            Handler::handle($stanza);
                        }

                        $this->deliver();
                    }
                } else {
                    $this->pack($presence->roster);

                    if ($presence->value == 5 && !empty($presence->resource)) {
                        $presence->delete();
                    }
                }

                $this->deliver();
            });
        }
    }
}
