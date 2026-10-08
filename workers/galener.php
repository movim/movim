<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Workers\Galener\Galener;
use Movim\Bootstrap;
use React\EventLoop\Loop;

$bootstrap = new Bootstrap;
$bootstrap->boot(true);

$loop = Loop::get();

$galener = new Galener;

$shutdown = function () use ($loop, $galener) {
    $galener->shutdown();
    $loop->stop();
};

$loop->addSignal(SIGTERM, $shutdown);
$loop->addSignal(SIGINT, $shutdown);

$loop->run();
