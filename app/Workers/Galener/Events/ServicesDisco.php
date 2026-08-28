<?php

namespace App\Workers\Galener\Events;

use App\Workers\Galener\Galener;
use DOMDocument;

class ServicesDisco extends Event
{
    public static function getHandlerPaths(): array
    {
        return ['iq|services{urn:xmpp:extdisco:2}'];
    }

    public function handle(): ?DOMDocument
    {
        $services = [];
        $stunServices = ['urls' => []];
        $turnServices = ['urls' => []];

        foreach ($this->node->stanza->services->service as $service) {
            if ($service->attributes()->type == 'stun') {
                array_push($stunServices['urls'], 'stun:' . $service->attributes()->host . ':' . $service->attributes()->port);
            }

            if ($service->attributes()->type == 'turn') {
                array_push($turnServices['urls'], 'turn:' . $service->attributes()->host . ':' . $service->attributes()->port);
                $turnServices['username'] = (string)$service->attributes()->username;
                $turnServices['credential'] = (string)$service->attributes()->password;
            }
        }

        if (!empty($stunServices['urls'])) {
            array_push($services, $stunServices);
        }

        if (!empty($turnServices['urls'])) {
            array_push($services, $turnServices);
        }

        file_put_contents(Galener::DATA_CACHE . 'ice-servers.json', json_encode($services));

        return null;
    }
}
