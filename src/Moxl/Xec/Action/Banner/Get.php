<?php

namespace Moxl\Xec\Action\Banner;

use App\Contact;
use Moxl\Stanza\Avatar;
use Moxl\Xec\Action;
use React\Http\Message\Response;

class Get extends Action
{
    protected $_to;
    protected $_node = false;

    public function request()
    {
        $this->store();
        $this->iq(Avatar::get('urn:xmpp:movim-banner:0'), to: $this->_to, type: 'get');
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        if (
            isset($stanza->pubsub->items->item->metadata->info)
            && isset($stanza->pubsub->items->item->metadata->info->attributes()->url)
        ) {
            $info = $stanza->pubsub->items->item->metadata->info->attributes();
            $contact = Contact::firstOrNew(['id' => $this->_to]);

            if ($info->id != $contact->bannerhash) {
                requestAvatarUrl(jid: $contact->id, url: (string) $info->url, banner: true)->then(
                    function (Response $response) use ($contact) {
                        $this->pack($contact);
                        $this->deliver();
                    }
                );
            }
        }
    }
}
