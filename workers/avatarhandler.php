<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Workers\AvatarHandler\AvatarHandler;
use Movim\Bootstrap;
use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\Loop;
use React\Http\HttpServer;
use React\Http\Message\Response;
use React\Promise\Promise;
use React\Socket\SocketServer;

$bootstrap = new Bootstrap;
$bootstrap->boot(true);

$loop = Loop::get();

$resolver = new AvatarHandler;

$handler = function (ServerRequestInterface $request) use ($resolver) {
    return new Promise(function ($resolve) use ($request, $resolver) {
        $query = null;
        $data = json_decode((string) $request->getBody());

        if (! $data) {
            $resolve(new Response(400));
            return;
        }

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
                    type: $data->type,
                    token: $data->token
                );
                break;
        }

        if ($query) {
            $query->then(function ($data) use ($resolve) {
                $resolve(Response::json($data));
            }, function (\Throwable $th) use ($resolve) {
                \logError($th);
                $resolve(new Response(500));
            });
        } else {
            $resolve(new Response(404));
        }
    });
};

$server = new HttpServer($handler);
$server->on('error', function (Throwable $e) {
    \logError($e);
});

$path = 'unix://' . AVATAR_HANDLER_SOCKET;
$server->listen(new SocketServer($path));

$loop->run();
