<?php

namespace Moxl\Xec\Action\Avatar;

use Moxl\Xec\Action;
use Moxl\Stanza\Avatar;

class Get extends Action
{
    private $_to;
    private $_me = false;

    public function request()
    {
        $this->store();
        Avatar::get($this->_to);
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

    public function handle($stanza, $parent = false)
    {
        $contact = \App\Contact::firstOrNew(['id' => $this->_to]);
        $contact->photobin  = (string)$stanza->pubsub->items->item->data;
        $contact->avatarhash = sha1(base64_decode($contact->photobin));
        $contact->createThumbnails();
        $contact->save();

        $this->pack($contact);
        $this->deliver();
    }
}
