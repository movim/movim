<?php

namespace Moxl\Xec\Payload;

use Moxl\Xec\Action\Vcard\Get;

use Movim\Session;

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
            $p = new \Modl\Presence;
            $p->setPresence($stanza);

            $pd = new \Modl\PresenceDAO;
            $pd->set($p);

            /*if($p->photo) {
                $r = new Get;
                $r->setTo(echapJid((string)$stanza->attributes()->from))->request();
            }*/

            if($p->muc
            && isset($stanza->x)
            && isset($stanza->x->status)) {
                $code = (string)$stanza->x->status->attributes()->code;
                if(isset($code) && $code == '110') {
                    if($p->value != 5 && $p->value != 6) {
                        $this->method('muc_handle');
                        $this->pack($p);
                    } elseif($p->value == 5) {
                        $pd->clearMuc($p->jid);

                        $this->method('unavailable_handle');
                        $this->pack($p);
                        $this->deliver();
                    }
                }
            } else {
                $cd = new \Modl\ContactDAO;
                $c = $cd->getRosterItem($p->jid, true);

                $this->pack($c);

                if($p->value == 5 /*|| $p->value == 6*/) {
                    $pd->delete($p);
                }
            }

            $this->deliver();
        }
    }
}
