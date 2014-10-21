<?php
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Movim\Daemon\Behaviour;

require dirname(__FILE__) . '/vendor/autoload.php';

$server = new Behaviour;

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            $server
        )
    ),
    8080
);

$server->run();
