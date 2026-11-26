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
        $jid = $this->_to ?? $this->me->id;
        $data = (string) $stanza->pubsub->items->item->data;

        if (empty($data)) {
            return;
        }

        requestAvatarBase64(
            jid: $jid,
            base64: $data,
            type: Avatar::NODE_DATA
        )->then(
            function (Response $response) use ($jid) {
                $this->pack(Contact::firstOrNew(['id' => $jid]));
                $this->deliver();
            }
        );
    }
}
