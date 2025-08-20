<?php

namespace Moxl\Xec\Action\OMEMO;

use Moxl\Xec\Action;
use Moxl\Stanza\OMEMO;

class GetDevicesList extends Action
{
    protected $_to;

    public function request()
    {
        $this->store();
        OMEMO::GetDevicesList($this->_to);
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $devicesIds = [];

        foreach ($stanza->pubsub->items->item as $item) {
            if ((string)$item->attributes()->id == 'current' || $stanza->pubsub->items->count() == 1) {
                foreach ($item->list->device as $device) {
                    array_push($devicesIds, (string)$device->attributes()->id);
                }
            }
        }

        $devicesIds = array_unique($devicesIds);

        $deviceCount = 0;
        $devicesCount = count($devicesIds);

        foreach ($devicesIds as $deviceId) {
            $deviceCount++;
            $gb = new GetBundle;
            $gb->setTo($this->_to)
                ->setId($deviceId);

            /**
             * We send a notification when the last bundle is retrieved
             */
            if ($devicesCount == $deviceCount) {
                $gb = $gb->notifyLast();
            }

            $gb->request();
        }

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
