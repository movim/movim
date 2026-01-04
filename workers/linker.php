<?php

require __DIR__ . '/../vendor/autoload.php';

gc_enable();

use Movim\Bootstrap;
use Movim\Session;

use App\PresenceBuffer;
use Movim\Daemon\Linker;
use Movim\Widget\Wrapper;
use Movim\Scheduler;

$loop = React\EventLoop\Loop::get();

$bootstrap = new Bootstrap;
$bootstrap->boot();

// DNS
$config = React\Dns\Config\Config::loadSystemConfigBlocking();
$server = $config->nameservers ? reset($config->nameservers) : '8.8.8.8';

$factory = new React\Dns\Resolver\Factory();
$dns = $factory->create($server);

// Scheduler
Scheduler::getInstance()->start();

// TCP Connector
$connector = null;

// We load and register all the widgets
$wrapper = \Movim\Widget\Wrapper::getInstance();
$wrapper->registerAll($bootstrap->getWidgets());

$timestampReceive = $timestampSend = $sqlQueryExecuted = time();

function handleSSLErrors($errno, $errstr)
{
    logOut(colorize('SSL Error ' . $errno . ': ' . $errstr, 'red'));
}

$linker = new Linker(getenv('sid'), $dns);

// Temporary linker killer
$loop->addPeriodicTimer(5, function () use (&$linker, &$timestampReceive, &$timestampSend) {
    if (($timestampSend < time() - 3600 * 24 /* 24h */ || $timestampReceive < time() - 60 * 30 /* 30min */)
        && $linker->connected()
    ) {
        $linker->logout();
    }
});

// Buffer timers
$loop->addPeriodicTimer(1, function () use ($linker) {
    $pb = PresenceBuffer::getInstance($linker->user);
    $pb->save();
});

$wsSocket = null;

function writeOut($msg = null)
{
    global $wsSocket;

    if (!empty($msg)) {
        $wsSocket->send(json_encode($msg));
    }
}

function logOut($log, string $type = 'system')
{
    fwrite(STDERR, colorize(getenv('sid'), 'turquoise') . ' ' . colorize($type, 'purple'). "   \n" . $log . "\n");
}

function writeXMPP($xml)
{
    global $timestampSend;
    global $linker;

    if (!empty($xml)) {
        $timestampSend = time();
        $linker->writeXMPP($xml);
    }
}

function shutdown()
{
    global $loop;
    global $wsSocket;

    logOut(colorize('Shutdown', 'blue'));

    $wsSocket->close();
    $loop->stop();
}

$wsSocketBehaviour = function ($msg) use (&$linker, &$connector, &$xmppBehaviour, &$dns) {
    $msg = json_decode($msg);

    if (isset($msg)) {
        switch ($msg->func) {
            case 'message':
                $linker->handleJSON($msg->b);
                break;

            case 'up':
            case 'down':
                if ($linker->connected()) {
                    Wrapper::getInstance()->iterate('session_' . $msg->func);
                }
                break;

            case 'unregister':
                $linker->logout();
                shutdown();
                break;

            case 'register':
                // Set the host, useful for the CN certificate check
                $session = Session::instance();

                // If the host is already set, we already launched the registration process
                if ($session->get('host')) {
                    return;
                }

                $session->set('host', $msg->host);
                $linker->register($msg->host);

                break;
        }
    }

    return;
};

$wsConnector = new \Ratchet\Client\Connector($loop);
$wsConnector('ws://127.0.0.1:' . config('daemon.port'), [], [
    'MOVIM_SESSION_ID' => getenv('sid'),
    'MOVIM_DAEMON_KEY' => getenv('key')
])->then(function (Ratchet\Client\WebSocket $socket) use (&$wsSocket, &$linker, $wsSocketBehaviour) {
    $wsSocket = $socket;
    $linker->attachWebsocket($socket);
    $wsSocket->on('message', $wsSocketBehaviour);
});

$loop->run();
