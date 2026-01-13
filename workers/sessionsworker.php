<?php

require __DIR__ . '/../vendor/autoload.php';

gc_enable();

use Movim\Bootstrap;
use Movim\Daemon\LinkersManager;
use Movim\Scheduler;

$loop = React\EventLoop\Loop::get();

$bootstrap = new Bootstrap;
$bootstrap->boot();

$linkersManager = new LinkersManager;

// Scheduler
Scheduler::getInstance()->start();

// We load and register all the widgets
$wrapper = \Movim\Widget\Wrapper::getInstance();
$wrapper->registerAll($bootstrap->getWidgets());

$sqlQueryExecuted = time();

function handleSSLErrors($errno, $errstr)
{
    logOut(colorize('SSL Error ' . $errno . ': ' . $errstr, 'red'));
}

function logOut($log = '', string $type = 'system', ?string $sid = null)
{
    $out = colorize($sid ?? '', 'yellow') . ' ' . colorize($type, 'purple') . "   \n";
    if (!empty($log)) $out .= $log . "\n";

    fwrite(STDERR, $out);
}

$wsConnector = new \Ratchet\Client\Connector($loop);
$wsConnector('ws://127.0.0.1:' . config('daemon.port'), [], [
    'MOVIM_WORKER_ID' => getenv('wid'),
    'MOVIM_DAEMON_KEY' => getenv('key')
])->then(function (Ratchet\Client\WebSocket $socket) use (&$linkersManager) {
    $linkersManager->attachWebsocket($socket);

    $socket->on('message', function ($msg) use (&$linkersManager) {
        $msg = json_decode($msg);
        if (isset($msg)) $linkersManager->handleMessage($msg);
    });
});

$loop->run();
