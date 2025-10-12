<?php

require __DIR__ . '/../vendor/autoload.php';

$bootstrap = new Movim\Bootstrap;
$bootstrap->boot(true);

use App\Workers\AvatarHandler\AvatarHandler;

use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\Loop;
use React\Http\HttpServer;
use React\Http\Message\Response;
use React\Promise\Promise;
use React\Socket\SocketServer;

$loop = Loop::get();

$resolver = new AvatarHandler;

$handler = function (ServerRequestInterface $request) use ($resolver) {
    return new Promise(function ($resolve) use ($request, $resolver) {
        $query = null;
        $data = json_decode((string)$request->getBody());

        if (!$data) return;

        switch ($request->getUri()->getPath()) {
            case '/url':
                $query = $resolver->url(
                    jid: $data->jid,
                    url: $data->url,
                    node: $data->node,
                    banner: $data->banner
                );
                break;

            case '/base64':
                $query = $resolver->base64(
                    jid: $data->jid,
                    type: $data->type
                );
                break;
        }

        if ($query) {
            $query->then(function ($data) use ($resolve) {
                $resolve(Response::json($data));
            });
        }
    });
};

$server = new HttpServer($handler);
$server->on('error', function (\Throwable $e) {
    \logError($e);
});

$path = 'unix://' . AVATAR_HANDLER_SOCKET;
$server->listen(new SocketServer($path));

$loop->run();
