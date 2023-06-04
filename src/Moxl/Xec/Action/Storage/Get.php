<?php

namespace Moxl\Xec\Action\Storage;

use Moxl\Xec\Action;

use App\User;
use Moxl\Stanza\Pubsub;

class Get extends Action
{
    private $_xmlns = 'movim:prefs';

    public function request()
    {
        $this->store();
        Pubsub::getItem(false, $this->_xmlns, 'current');
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        if ($stanza->pubsub->items->item) {
            $data = unserialize(trim((string)$stanza->pubsub->items->item->data));

            if (is_array($data)) {
                $me = User::me();
                $me->setConfig($data);
                $me->save();
            }

            $this->pack($data);
            $this->deliver();
        }
    }

    public function error(string $errorId, ?string $message = null)
    {
        $this->deliver();
    }
}
