<?php

namespace Moxl\Xec\Action\ExternalServices;

use Moxl\Xec\Action;
use Moxl\Stanza\ExternalServices;

class Get extends Action
{
    protected $_to;

    public function request()
    {
        $this->store();
        ExternalServices::request($this->_to);
    }

    public function handle($stanza, $parent = false)
    {
        $services = [];
        foreach ($stanza->services->service as $service) {
            $item = [
                'host' => (string)$service['host'],
                'port' => (string)$service['port'],
                'transport' => (string)$service['transport'],
                'type' => (string)$service['type']
            ];

            if ($service['username'] && $service['password']) {
                $item['username'] = (string)$service['username'];
                $item['password'] = (string)$service['password'];
            }

            array_push($services, $item);
        }

        if (!empty($services)) {
            $this->pack($services);
            $this->deliver();
        }
    }

    public function error($stanza, $parent = false)
    {
        $this->deliver();
    }
}