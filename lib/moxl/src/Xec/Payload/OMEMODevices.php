<?php

namespace Moxl\Xec\Payload;

use Moxl\Xec\Action\OMEMO\GetBundle;

class OMEMODevices extends Payload
{
    public function handle($stanza, $parent = false)
    {
        $from   = (string)$parent->attributes()->from;

        $list = $stanza->items->item->list;

        if ($list) {
            foreach ($list as $devices) {
                foreach ($devices as $device) {
                    $gb = new GetBundle;
                    $gb->setTo($from)
                       ->setId((string)$device->attributes()->id)
                       ->request();
                }
            }

            $this->deliver();
        }
    }
}
