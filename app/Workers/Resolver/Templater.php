<?php

namespace App\Workers\Resolver;

use Movim\Widget\Wrapper;
use Moxl\Xec\Payload\Packet;
use React\Promise\Promise;
use stdClass;

class Templater
{
    public function callWidget(string $jid, string $widget, string $method, \stdClass $data): Promise
    {
        return new Promise(function ($resolve) use ($jid, $widget, $method, $data) {
            try {
                (new Wrapper)->runUserWidget(
                    $jid,
                    $widget,
                    $method,
                    $data ? (new Packet)->pack($data->content, $data->from ?? null) : null
                );

                $resolve();
            } catch (\Throwable $th) {
                \logError($widget . '_' . $method . ': ' . $th);
            }
        });
    }
}
