#!/usr/bin/env php

<?php
require dirname(__FILE__) . '/vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

use Movim\Daemon\Core;
use Movim\Daemon\Api;
use Movim\Bootstrap;

use \React\EventLoop\Factory;
use React\Socket\Server as Reactor;

define('DOCUMENT_ROOT', dirname(__FILE__));

$bootstrap = new Bootstrap;
$booted = $bootstrap->boot();

$argsize = count($argv);
if($argsize == 1) {
    echo colorize("Please specify a base uri eg.", "red"). colorize(" http://myhost.com/movim/\n", 'yellow');
    exit;
}

if($argsize == 2) {
    echo colorize("Please specify a port eg.", "red"). colorize(" 8080\n", 'yellow');
    exit;
}

$md = Modl\Modl::getInstance();
$infos = $md->check();

if($infos != null) {
    echo colorize("The database need to be updated before running the daemon\n", 'green');
    foreach($infos as $i) {
        echo colorize($i."\n", 'blue');
    }

    echo colorize("\nTo update the database run\n", 'green');
    echo colorize("php mud.php db --set\n", 'purple');
    exit;
}

$loop = Factory::create();
$core = new Core($loop, $argv[1], $argv[2]);
$app  = new HttpServer(new WsServer($core));

$socket = new Reactor($loop);
$socket->listen($argv[2], '0.0.0.0');

$socketApi = new Reactor($loop);
new Api($socketApi, $core);
$socketApi->listen(1560);

$server = new IoServer($app, $socket, $loop);

$server->run();
