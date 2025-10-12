<?php

namespace Moxl\Xec\Action\Banner;

use Moxl\Xec\Action;
use Moxl\Stanza\Avatar;
use React\Http\Message\Response;

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
        if (
            isset($stanza->pubsub->items->item->metadata->info)
            && isset($stanza->pubsub->items->item->metadata->info->attributes()->url)
        ) {
            $info = $stanza->pubsub->items->item->metadata->info->attributes();
            $contact = \App\Contact::firstOrNew(['id' => $this->_to]);

            if ($info->id != $contact->bannerhash) {
                requestAvatarUrl(jid: $contact->id, url: (string)$info->url, banner: true)->then(
                    function (Response $response) use ($contact) {
                        $this->pack($contact);
                        $this->deliver();
                    }
                );
            }
        }
    }
}
