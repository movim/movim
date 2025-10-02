<?php

require __DIR__ . '/../vendor/autoload.php';

$bootstrap = new Movim\Bootstrap;
$bootstrap->boot(true);

use App\Workers\Pusher\Pusher;
use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\Loop;
use React\Http\HttpServer;
use React\Promise\Promise;
use React\Socket\SocketServer;

$loop = Loop::get();
$pusher = new Pusher;

$handler = function (ServerRequestInterface $request) use ($pusher) {
    $data = json_decode((string)$request->getBody());

    return new Promise(function () use ($data, $pusher) {
        $pusher->send(
            $data->user_id,
            $data->title,
            $data->body,
            $data->picture,
            $data->action,
            $data->group,
            $data->execute
        );
    });
};

$server = new HttpServer($handler);
$server->on('error', function (\Throwable $e) {
    \logError($e);
});

$path = 'unix://' . PUSHER_SOCKET;
$server->listen(new SocketServer($path));
