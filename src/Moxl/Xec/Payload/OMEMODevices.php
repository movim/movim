<?php

namespace Moxl\Xec\Payload;

use Moxl\Xec\Action\OMEMO\GetBundle;

class OMEMODevices extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $from = (string)$parent->attributes()->from;
        $list = $stanza->items->item->list;

        if ($list) {
            $devicesIds = [];

            foreach ($list as $devices) {
                foreach ($devices as $device) {
                    array_push($devicesIds, (string)$device->attributes()->id);
                }
            }

            // If we have several time the same ID...
            $devicesIds = array_unique($devicesIds);

            // Refresh our own devices
            if ($from == me()->id) {
                foreach ($devicesIds as $deviceId) {
                    $gb = new GetBundle;
                    $gb->setTo($from)
                        ->setId($deviceId)
                        ->request();
                }
            }

            $this->pack([
                'from' => $from,
                'devices' => $devicesIds
            ]);
            $this->deliver();
        }
    }
}
