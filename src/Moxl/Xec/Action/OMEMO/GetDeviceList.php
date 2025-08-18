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

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $devicesIds = [];

        foreach ($stanza->pubsub->items->item as $item) {
            if ((string)$item->attributes()->id == 'current' || $stanza->pubsub->items->count() == 1) {
                $devicesCount = $item->list->device->count();
                $deviceCount = 0;

                foreach ($item->list->device as $device) {
                    $deviceCount++;

                    $gb = new GetBundle;
                    $gb->setTo($this->_to)
                        ->setId((string)$device->attributes()->id);

                    array_push($devicesIds, (string)$device->attributes()->id);

                    /**
                     * We send a notification when the last bundle is retrieved
                     */
                    if ($devicesCount == $deviceCount) {
                        $gb = $gb->notifyLast();
                    }

                    $gb->request();
                }
            }
        }

        $devicesIds = array_unique($devicesIds);

        $this->pack([
            'from' => $this->_to,
            'devices' => $devicesIds
        ]);
        $this->deliver();
    }

    public function error(string $errorId, ?string $message = null)
    {
        $this->pack($this->_to);
        $this->deliver();
    }
}
