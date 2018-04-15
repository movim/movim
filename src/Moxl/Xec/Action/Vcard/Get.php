<?php

namespace Moxl\Xec\Action\Vcard;

use Moxl\Xec\Action;
use Moxl\Stanza\Vcard;

class Get extends Action
{
    private $_to;
    private $_me = false;
    private $_muc = false;

    public function request()
    {
        $this->store();
        Vcard::get($this->_to);
    }

    public function setTo($to)
    {
        $this->_to = $to;
        return $this;
    }

    public function setMe()
    {
        $this->_me = true;
        return $this;
    }

    public function isMuc()
    {
        $this->_muc = true;
        return $this;
    }

    public function handle($stanza, $parent = false)
    {
        if($stanza->attributes()->from) {
            $jid = current(explode('/',(string)$stanza->attributes()->from));
        } else {
            $jid = $this->_to;
        }

        if($this->_muc) {
            //$c = new \Modl\Conference;
            //$c->setAvatar($stanza, $this->_to);
        } elseif($jid) {
            $contact = \App\Contact::firstOrNew(['id' => $this->_to]);
            $contact->set($stanza, $this->_to);
            $contact->createThumbnails();
            $contact->save();

            $this->pack($contact);
            $this->deliver();
        }
    }
}
