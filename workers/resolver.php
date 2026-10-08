<?php

require __DIR__ . '/../vendor/autoload.php';

$bootstrap = new Movim\Bootstrap;
$bootstrap->boot(true);

use App\Workers\Resolver\Resolver;

use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\Loop;
use React\Http\HttpServer;
use React\Http\Message\Response;
use React\Promise\Promise;
use React\Socket\SocketServer;

$loop = Loop::get();

$resolver = new Resolver;

$handler = function (ServerRequestInterface $request) use ($resolver) {
    $data = json_decode((string)$request->getBody());

    return new Promise(function ($resolve) use ($data, $resolver) {
        $resolver->resolve($data->url)->then(function ($extractor) use ($resolve) {
            $resolve(Response::json($extractor));
        });
    });
};

$server = new HttpServer($handler);
$server->on('error', function (\Throwable $e) {
    \logError($e);
});

$path = 'unix://' . RESOLVER_SOCKET;
//$path = '127.0.0.1:8899';
$server->listen(new SocketServer($path));

$loop->run();
