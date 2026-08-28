<?php

require __DIR__ . '/../vendor/autoload.php';

$bootstrap = new Movim\Bootstrap;
$bootstrap->boot(true);

use App\Workers\Galener\Galener;
use React\EventLoop\Loop;

$loop = Loop::get();

$galener = new Galener;

$shutdown = function () use ($loop, $galener) {
    $galener->shutdown();
    $loop->stop();
};

$loop->addSignal(SIGTERM, $shutdown);
$loop->addSignal(SIGINT, $shutdown);

$loop->run();
