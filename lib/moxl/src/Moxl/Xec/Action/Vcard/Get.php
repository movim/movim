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
        $contact = \App\Contact::firstOrNew(['id' => $this->_to]);
        $contact->set($stanza, $this->_to);
        $contact->createThumbnails();
        $contact->save();

        $this->pack($contact);
        $this->deliver();
    }
}
