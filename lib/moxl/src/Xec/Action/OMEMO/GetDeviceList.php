<?php

namespace Moxl\Xec\Action\OMEMO;

use Moxl\Xec\Action;
use Moxl\Stanza\OMEMO;

class GetDeviceList extends Action
{
    protected $_to;

    public function request()
    {
        $this->store();
        OMEMO::getDeviceList($this->_to);
    }

    public function handle($stanza, $parent = false)
    {
        \App\User::me()->bundles()->where('jid', $this->_to)->delete();

        foreach ($stanza->pubsub->items->item as $item) {
            if ((string)$item->attributes()->id == 'current' || $stanza->pubsub->items->count() == 1) {
                foreach ($item->list->device as $device) {
                    $gb = new GetBundle;
                    $gb->setTo($this->_to)
                        ->setId((string)$device->attributes()->id)
                        ->request();
                }
            }
        }
    }
}
