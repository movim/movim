<?php
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Movim\Daemon\Behaviour;

require dirname(__FILE__) . '/vendor/autoload.php';

$argsize = count($argv);
if($argsize == 1) {
    echo "Please specify a base uri eg. http://myhost.com/movim/\n";
    exit;
}

$server = new Behaviour($argv[1]);

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            $server
        )
    ),
    8080
);

$server->run();
