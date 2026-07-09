<?php

require __DIR__ . '/../vendor/autoload.php';

$bootstrap = new Movim\Bootstrap;
$bootstrap->boot(true);

use App\Workers\Galener\Galener;
use React\EventLoop\Loop;

$loop = Loop::get();

$galener = new Galener;

$loop->run();
