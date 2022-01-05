<?php

namespace Moxl\Xec\Payload;

use App\Bundle;
use Moxl\Xec\Action\OMEMO\GetBundle;

class OMEMODevices extends Payload
{
    public function handle($stanza, $parent = false)
    {
        $from   = (string)$parent->attributes()->from;
        $list = $stanza->items->item->list;

        if ($list) {
            $devicesIds = [];

            foreach ($list as $devices) {
                foreach ($devices as $device) {
                    array_push($devicesIds, (string)$device->attributes()->id);
                }
            }

            // Remove all the cached devices not in the list
            Bundle::where('user_id', \App\User::me()->id)
                  ->where('jid', $from)
                  ->whereNotIn('bundleid', $devicesIds)
                  ->delete();

            // Refresh the rest
            foreach ($devicesIds as $deviceId) {
                $gb = new GetBundle;
                $gb->setTo($from)
                   ->setId($deviceId)
                   ->request();
            }

            $this->pack([
                'from' => $from,
                'devices' => $devicesIds
            ]);
            $this->deliver();
        }
    }
}
