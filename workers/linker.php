<?php

require __DIR__ . '/../vendor/autoload.php';

gc_enable();

use Movim\Bootstrap;
use Movim\Daemon\LinkerManager;
use Movim\Scheduler;

$loop = React\EventLoop\Loop::get();

$bootstrap = new Bootstrap;
$bootstrap->boot();

$linkerManager = new LinkerManager;

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

$wsSocket = null;

function logOut($log, ?string $sid = null, string $type = 'system')
{
    fwrite(STDERR, colorize($sid ?? '', 'turquoise') . ' ' . colorize($type, 'purple') . "   \n" . $log . "\n");
}

$wsConnector = new \Ratchet\Client\Connector($loop);
$wsConnector('ws://127.0.0.1:' . config('daemon.port'), [], [
    'MOVIM_SESSION_ID' => getenv('sid'),
    'MOVIM_DAEMON_KEY' => getenv('key')
])->then(function (Ratchet\Client\WebSocket $socket) use (&$wsSocket, &$linkerManager) {
    $wsSocket = $socket;
    $linkerManager->attachWebsocket($socket);

    // Temporary
    $msg = new \stdClass;
    $msg->func = 'new';
    $msg->sid = getenv('sid');

    $msg->browserLocale = getenv('language');
    $linkerManager->handleMessage($msg);

    $wsSocket->on('message', function ($msg) use (&$linkerManager) {
        $msg = json_decode($msg);
        if (isset($msg)) $linkerManager->handleMessage($msg);
    });
});

$loop->run();
