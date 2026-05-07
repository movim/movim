<?php

namespace Moxl\Xec\Payload;

use App\Presence as DBPresence;
use App\Widgets\Notif\Notif;
use Moxl\Xec\Action\Presence\Muc;
use Moxl\Xec\Handler;

class Presence extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $jid = explodeJid($stanza->attributes()->from);

        if ($this->me?->hasBlocked($jid['jid'])) {
            return;
        }

        if (
            (string)$stanza->attributes()->type === 'error'
            && isset($stanza->attributes()->id)
        ) {
            // Let's drop errors with an id, useless for us
        } else {
            $presence = (new DBPresence);
            if (!$presence->set($this->me, $stanza)) return;

            /**
             * Trigger presence_muji when the <muji /> element is cleared and if the MUC is actually displayed
             */
            $wasMuji = false;

            $arr = explode('|', (new Notif($this->me, sessionId: $this->sessionId))->getCurrent());
            if (isset($arr[1]) && $presence->jid == $arr[1]) {
                $dbPresence = linker($this->sessionId)->user?->session?->presences()
                    ->where('jid', $presence->jid)
                    ->where('mucjid', $presence->mucjid)
                    ->where('resource', $presence->resource)
                    ->whereNotNull('muji_xml')
                    ->first();
                if ($dbPresence) {
                    $wasMuji = true;
                }
            }

            // Trigger the presence before the buffer, we need it before the Jingle messages
            if ($presence->hasMuji() || $wasMuji) {
                $this->pack($presence, $presence->mucjid . '/' . $presence->mucjidresource);
                $this->method($wasMuji ? 'was_muji' : 'muji');
                $this->deliver();
            }

            linker($this->sessionId)->presenceBuffer->append(
                $presence,
                function () use ($presence, $stanza) {
                    if ((string)$stanza->attributes()->type == 'subscribe') {
                        $this->pack((string)$stanza->attributes()->from);
                        $this->event('subscribe');
                    }

                    if ($presence->muc) {

                        if ($presence->mucjid == $this->me->id) {
                            // Spectrum2 specific bug, we can receive two self-presences, one with several caps items
                            $cCount = 0;
                            foreach ($stanza->children() as $key => $content) {
                                if ($key == 'c') $cCount++;
                            }

                            if ($cCount > 1) {
                                $presence->delete();
                            }
                            // So we drop it

                            $session = linker($this->sessionId)->session;

                            if ($presence->value != 5 && $presence->value != 6) {
                                $this->method('muc_handle');
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
                                (new Handler($this->me, sessionId: $this->sessionId))->handle($stanza);
                            }
                        }
                    } elseif ($presence->value == 5 && !empty($presence->resource)) {
                        $presence->delete();
                    }

                    /**
                     * Don't handle for MUC presences before we are fully authenticated
                     */
                    if (!$presence->muc || linker($this->sessionId)->chatroomPings->has($presence->jid)) {
                        $this->pack($presence);
                        $this->deliver();
                    }
                }
            );
        }
    }
}
