<?php

namespace Moxl\Xec\Action\Banner;

use Movim\Image;
use Moxl\Xec\Action;
use Moxl\Stanza\Avatar;

class Get extends Action
{
    protected $_to;
    protected $_node = false;

    public function request()
    {
        $this->store();
        Avatar::get($this->_to, 'urn:xmpp:movim-banner:0');
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        if (isset($stanza->pubsub->items->item->metadata->info)
         && isset($stanza->pubsub->items->item->metadata->info->attributes()->url)) {
            $info = $stanza->pubsub->items->item->metadata->info->attributes();

            $contact = \App\Contact::firstOrNew(['id' => $this->_to]);

            if ($info->id != $contact->bannerhash) {
                $contact->bannerhash = $info->id;
                $contact->save();

                $p = new Image;

                if ($p->fromURL((string)$info->url)) {
                    $p->setKey($this->_to . '_banner');
                    $p->save();

                    $this->pack($contact);
                    $this->deliver();
                }
            }
        }
    }
}
