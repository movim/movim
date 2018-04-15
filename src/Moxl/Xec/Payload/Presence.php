<?php

namespace Moxl\Xec\Payload;

use Moxl\Xec\Action\Vcard\Get;

use Movim\Session;
use App\Presence as DBPresence;

class Presence extends Payload
{
    public function handle($stanza, $parent = false)
    {
        // Subscribe request
        if((string)$stanza->attributes()->type == 'subscribe') {
            $session = Session::start();
            $notifs = $session->get('activenotifs');
            $notifs[(string)$stanza->attributes()->from] = 'sub';
            $session->set('activenotifs', $notifs);

            $this->event('subscribe', (string)$stanza->attributes()->from);
        } else {
            $presence = DBPresence::findByStanza($stanza);
            $presence->set($stanza);
            $presence->save();

            /*if($p->photo) {
                $r = new Get;
                $r->setTo(echapJid((string)$stanza->attributes()->from))->request();
            }*/

            if($presence->muc
            && isset($stanza->x)
            && isset($stanza->x->status)) {
                $code = (string)$stanza->x->status->attributes()->code;
                if(isset($code) && $code == '110') {
                    if($presence->value != 5 && $presence->value != 6) {
                        $this->method('muc_handle');
                        $this->pack($presence);
                    } elseif($p->value == 5) {
                        //$pd->clearMuc($p->jid);

                        $this->method('unavailable_handle');
                        $this->pack($presence);
                        $this->deliver();
                    }
                }
            } else {
                $this->pack($presence->roster);

                if($presence->value == 5 /*|| $p->value == 6*/) {
                    $presence->delete();
                }
            }

            $this->deliver();
        }
    }
}
