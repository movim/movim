<?php
require __DIR__ . '/vendor/autoload.php';

gc_enable();

use Movim\Bootstrap;
use Movim\RPC;
use Movim\Session;

$bootstrap = new Bootstrap;
$bootstrap->boot();

$loop = React\EventLoop\Factory::create();

$connector = new React\Socket\TimeoutConnector(
    new React\Socket\TcpConnector($loop), 5.0, $loop
);

// DNS
$config = React\Dns\Config\Config::loadSystemConfigBlocking();
$server = $config->nameservers ? reset($config->nameservers) : '8.8.8.8';

$factory = new React\Dns\Resolver\Factory();
$dns = $factory->createCached($server, $loop);

// We load and register all the widgets
$wrapper = \Movim\Widget\Wrapper::getInstance();
$wrapper->registerAll($bootstrap->getWidgets());

$xmppSocket = null;

$parser = new \Moxl\Parser(function ($node) {
    \Moxl\Xec\Handler::handle($node);
});

$timestamp = time();

function handleSSLErrors($errno, $errstr)
{
    fwrite(
        STDERR,
        colorize(getenv('sid'), 'yellow').
        " : ".colorize($errno, 'red').
        " ".
        colorize($errstr, 'red').
        "\n"
    );
}

// Temporary linker killer
$loop->addPeriodicTimer(5, function () use (&$xmppSocket, &$timestamp) {
    if ($timestamp < time() - 3600*4
    && isset($xmppSocket)) {
        $xmppSocket->close();
    }
});

$wsSocket = null;

function writeOut($msg = null)
{
    global $wsSocket;

    if (!empty($msg)) {
        $wsSocket->send(json_encode($msg));
    }
}

function writeXMPP($xml)
{
    global $xmppSocket;

    if (!empty($xml) && $xmppSocket) {
        $xmppSocket->write(trim($xml));

        if (getenv('debug')) {
            fwrite(STDERR, colorize(trim($xml), 'yellow')." : ".colorize('sent to XMPP', 'green')."\n");
        }
    }
}

function shutdown()
{
    global $loop;
    global $wsSocket;

    $wsSocket->close();
    $loop->stop();
}

$wsSocketBehaviour = function ($msg) use (&$xmppSocket, &$connector, &$xmppBehaviour, &$dns) {
    global $wsSocket;

    $msg = json_decode($msg);

    if (isset($msg)) {
        switch ($msg->func) {
            case 'message':
                (new RPC)->handleJSON($msg->b);
                break;

            case 'ping':
                // And we say that we are ready !
                $obj = new \StdClass;
                $obj->func = 'pong';
                $wsSocket->send(json_encode($obj));
                break;

            case 'up':
            case 'down':
                if (isset($xmppSocket)
                && is_resource($xmppSocket->stream)) {
                    $evt = new Movim\Widget\Event;
                    $evt->run('session_'.$msg->func);
                }
                break;

            case 'unregister':
                \Moxl\Stanza\Stream::end();
                if (isset($xmppSocket)) {
                    $xmppSocket->close();
                }
                shutdown();
                break;

            case 'register':
                $port = 5222;
                $host = $msg->host;

                $dns->resolveAll('_xmpp-client._tcp.' . $msg->host, React\Dns\Model\Message::TYPE_SRV)
                    ->then(
                        function ($resolved) use (&$host, &$port, &$msg) {
                            $host = $resolved[0]['target'];
                            $port = $resolved[0]['port'];

                            if (getenv('verbose')) {
                                fwrite(
                                    STDERR,
                                    colorize(
                                        getenv('sid'),
                                        'yellow'
                                    )." : ".
                                        colorize('Resolved target '.$host.' from '.$msg->host, 'blue').
                                        "\n"
                                );
                            }
                        }
                    )
                    ->always(function () use (&$connector, &$xmppBehaviour, &$dns, &$host, &$port) {
                        $dns->resolve($host, React\Dns\Model\Message::TYPE_AAAA)
                            ->then(
                                function ($ip) use (&$connector, &$xmppBehaviour, $host, $port) {
                                    if (getenv('verbose')) {
                                        fwrite(
                                            STDERR,
                                            colorize(
                                                getenv('sid'),
                                                'yellow'
                                            )." : ".
                                                colorize('Connection to '.$host.' ('.$ip.')', 'blue').
                                                "\n"
                                        );
                                    }

                                    $connector->connect('['.$ip.']:'. $port)
                                              ->then($xmppBehaviour)
                                              ->otherwise(function () {
                                                  $evt = new Movim\Widget\Event;
                                                  $evt->run('timeout_error');
                                                  $this->cancel();
                                              });
                                }
                            )
                            ->otherwise(function () {
                                $evt = new Movim\Widget\Event;
                                $evt->run('dns_error');
                            });
                    });

                break;
        }
    }

    return;
};

$xmppBehaviour = function (React\Socket\Connection $stream) use (&$xmppSocket, $parser, &$timestamp) {
    global $wsSocket;

    $xmppSocket = $stream;

    if (getenv('verbose')) {
        fwrite(STDERR, colorize(getenv('sid'), 'yellow')." : ".colorize('XMPP socket launched', 'blue')."\n");
        fwrite(STDERR, colorize(getenv('sid'), 'yellow')." launched : ".\sizeToCleanSize(memory_get_usage())."\n");
    }

    $xmppSocket->on('data', function ($message) use (&$xmppSocket, $parser, &$timestamp) {
        if (!empty($message)) {
            $restart = false;

            if (getenv('debug')) {
                fwrite(STDERR, colorize($message, 'yellow')." : ".colorize('received', 'green')."\n");
            }

            if ($message == '</stream:stream>') {
                $xmppSocket->close();
                shutdown();
            } elseif ($message == "<proceed xmlns='urn:ietf:params:xml:ns:xmpp-tls'/>"
                  || $message == '<proceed xmlns="urn:ietf:params:xml:ns:xmpp-tls"/>') {
                $session = Session::start();
                stream_set_blocking($xmppSocket->stream, 1);
                stream_context_set_option($xmppSocket->stream, 'ssl', 'SNI_enabled', false);
                stream_context_set_option($xmppSocket->stream, 'ssl', 'peer_name', $session->get('host'));
                stream_context_set_option($xmppSocket->stream, 'ssl', 'allow_self_signed', true);

                // See http://php.net/manual/en/function.stream-socket-enable-crypto.php#119122
                $crypto_method = STREAM_CRYPTO_METHOD_TLS_CLIENT;

                if (defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')) {
                    $crypto_method |= STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
                    $crypto_method |= STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT;
                }

                set_error_handler('handleSSLErrors');
                $out = stream_socket_enable_crypto($xmppSocket->stream, 1, $crypto_method);
                restore_error_handler();

                if ($out !== true) {
                    $evt = new Movim\Widget\Event;
                    $evt->run('ssl_error');

                    shutdown();
                    return;
                }

                if (getenv('verbose')) {
                    fwrite(STDERR, colorize(getenv('sid'), 'yellow')." : ".colorize('TLS enabled', 'blue')."\n");
                }

                $restart = true;
            }

            $timestamp = time();

            if ($restart) {
                $session = Session::start();
                \Moxl\Stanza\Stream::init($session->get('host'));
                stream_set_blocking($xmppSocket->stream, 0);
                $restart = false;
            }

            if (!$parser->parse($message)) {
                fwrite(STDERR, colorize(getenv('sid'), 'yellow')." ".$parser->getError()."\n");
            }
        }
    });

    $xmppSocket->on('error', function () {
        shutdown();
    });
    $xmppSocket->on('close', function () {
        shutdown();
    });

    // And we say that we are ready !
    $obj = new \StdClass;
    $obj->func = 'registered';

    fwrite(STDERR, 'registered');
    $wsSocket->send(json_encode($obj));
};

$wsConnector = new \Ratchet\Client\Connector($loop);
$wsConnector('ws://localhost:' . getenv('port'), [], [
    'MOVIM_SESSION_ID' => getenv('sid'),
    'MOVIM_DAEMON_KEY' => getenv('key')
])->then(function (Ratchet\Client\WebSocket $socket) use (&$wsSocket, $wsSocketBehaviour) {
    $wsSocket = $socket;
    $wsSocket->on('message', $wsSocketBehaviour);
});

$loop->run();
