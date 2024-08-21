<?php

namespace Moxl\Xec\Action\Storage;

use Moxl\Xec\Action;

use App\User;
use Moxl\Stanza\Pubsub;
use Moxl\Stanza\Storage;

class Get extends Action
{
    public function request()
    {
        $this->store();
        Pubsub::getItem(false, Storage::$node, 'current');
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        if ($stanza->pubsub->items->item && $stanza->pubsub->items->item->x
        && $stanza->pubsub->items->item->x->attributes()->xmlns == 'jabber:x:data') {
            $config = [];

            foreach ($stanza->pubsub->items->item->x->field as $field) {
                $config[(string)$field->attributes()->var] = (string)$field->value == 'false'
                    ? false
                    : (string)$field->value;
            }

            if (!empty($config)) {
                $me = User::me();
                $me->setConfig($config);
                $me->save();
            }

            $this->pack($config);
            $this->deliver();
        }
    }

    public function error(string $errorId, ?string $message = null)
    {
        $this->deliver();
    }
}
