<?php

require __DIR__ . '/vendor/autoload.php';

$bootstrap = new Movim\Bootstrap;
$bootstrap->boot(true);

use App\Workers\Resolver\Templater;
use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\Loop;
use React\Http\HttpServer;
use React\Promise\Promise;
use React\Socket\SocketServer;

$loop = Loop::get();
$wsTemplaterSocket = null;
$templater = new Templater;

function writeTemplater($msg = null)
{
    global $wsTemplaterSocket;
    $wsTemplaterSocket->send(json_encode($msg));
}

/**
 * Launch and connect the templater
 */

$handler = function (ServerRequestInterface $request) use ($templater) {
    $data = json_decode((string)$request->getBody());

    return new Promise(function () use ($data, $templater) {
        $templater->callWidget($data->jid, $data->widget, $data->method, $data->data);
    });
};

$server = new HttpServer($handler);
$server->on('error', function (\Throwable $e) {
    \logError($e);
});

$path = 'unix://' . TEMPLATER_SOCKET;
$server->listen(new SocketServer($path));

/**
 * Authenticated Websocket to the main Daemon
 */
$wsConnector = new \Ratchet\Client\Connector($loop);
$wsConnector('ws://127.0.0.1:' . config('daemon.port'), [], [
    'MOVIM_DAEMON_KEY' => getenv('key'),
    'MOVIM_TEMPLATER' => 'templater',
])->then(function (Ratchet\Client\WebSocket $socket) use (&$wsTemplaterSocket) {
    $wsTemplaterSocket = $socket;
}, function ($e) {
    \logError($e->getMessage());
});

$loop->run();
