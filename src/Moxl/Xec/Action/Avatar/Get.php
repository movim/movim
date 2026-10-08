<?php

namespace Moxl\Xec\Action\Avatar;

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
        $this->iq(Avatar::get($this->_node), to: $this->_to, type: 'get');
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        requestAvatarBase64(
            jid: $this->_to,
            base64: (string) $stanza->pubsub->items->item->data,
            type: Avatar::NODE_DATA
        )->then(
            function (Response $response) {
                $this->pack(Contact::firstOrNew(['id' => $this->_to]));
                $this->deliver();
            }
        );
    }
}
