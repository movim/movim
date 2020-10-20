<?php
require __DIR__ . '/vendor/autoload.php';

gc_enable();

use Movim\Bootstrap;
use Movim\RPC;
use Movim\Session;

use React\Promise\Timer;

$loop = React\EventLoop\Factory::create();

$bootstrap = new Bootstrap;
$bootstrap->boot();

// DNS
$config = React\Dns\Config\Config::loadSystemConfigBlocking();
$server = $config->nameservers ? reset($config->nameservers) : '8.8.8.8';

$factory = new React\Dns\Resolver\Factory();
$dns = $factory->create($server, $loop);

// TCP Connector
$connector = new React\Socket\HappyEyeBallsConnector(
    $loop,
    new React\Socket\TcpConnector($loop, ['timeout' => 5.0]),
    $dns
);

// We load and register all the widgets
$wrapper = \Movim\Widget\Wrapper::getInstance();
$wrapper->registerAll($bootstrap->getWidgets());

$xmppSocket = null;
$directTLSSocket = false;

$parser = new \Moxl\Parser(function ($node) {
    \Moxl\Xec\Handler::handle($node);
});

$timestampReceive = $timestampSend = $sqlQueryExecuted = time();

function handleSSLErrors($errno, $errstr)
{
    logOut(colorize('SSL Error '.$errno.': '.$errstr, 'red'));
}

// Temporary linker killer
$loop->addPeriodicTimer(5, function () use (&$xmppSocket, &$timestampReceive, &$timestampSend) {
    if (($timestampSend < time() - 3600*24 /* 24h */ || $timestampReceive < time() - 60*30 /* 30min */)
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

function logOut($log)
{
    fwrite(STDERR, colorize(getenv('sid'), 'yellow')." : ".$log."\n");
}

function writeXMPP($xml)
{
    global $xmppSocket;
    global $timestampSend;

    if (!empty($xml) && $xmppSocket) {
        $timestampSend = time();
        $xmppSocket->write(trim($xml));

        if (getenv('debug')) {
            logOut(colorize(trim($xml).' ', 'yellow') . colorize('sent to XMPP', 'green'));
        }
    }
}

function enableEncryption($stream): bool
{
    logOut(colorize('Enable TLS on the socket', 'blue'));

    $session = Session::start();
    stream_set_blocking($stream, 1);
    stream_context_set_option($stream, 'ssl', 'SNI_enabled', true);
    stream_context_set_option($stream, 'ssl', 'peer_name', $session->get('host'));
    stream_context_set_option($stream, 'ssl', 'allow_self_signed', false);

    // See http://php.net/manual/en/function.stream-socket-enable-crypto.php#119122
    $crypto_method = STREAM_CRYPTO_METHOD_TLS_CLIENT;

    if (defined('STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT')) {
        $crypto_method |= STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT;
    }

    if (defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')) {
        $crypto_method |= STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
        $crypto_method |= STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT;
    }

    set_error_handler('handleSSLErrors');
    $out = stream_socket_enable_crypto($stream, 1, $crypto_method);
    restore_error_handler();

    if ($out !== true) {
        $evt = new Movim\Widget\Event;
        $evt->run('ssl_error');

        shutdown();
        return false;
    }

    return true;
}

function handleClientDNS(array $results, $dns, $connector, $xmppBehaviour)
{
    if (count($results) > 1) {
        $port = 5222;
        global $directTLSSocket;

        if ($results['directtls'] !== false && $results['directtls'][0]['target'] !== '.'
         && $results['starttls'] !== false && $results['starttls'][0]['target'] !== '.') {
            if ($results['starttls'][0]['priority'] > $results['directtls'][0]['priority']) {
                $host = $results['starttls'][0]['target'];
                $port = $results['starttls'][0]['port'];
                logOut(colorize('Picked STARTTLS', 'blue'));
            } else {
                $host = $results['directtls'][0]['target'];
                $port = $results['directtls'][0]['port'];
                $directTLSSocket = true;
                logOut(colorize('Picked DirectTLS', 'blue'));
            }
        } elseif ($results['directtls'] !== false && $results['directtls'][0]['target'] !== '.') {
            $host = $results['directtls'][0]['target'];
            $port = $results['directtls'][0]['port'];
            $directTLSSocket = true;
            logOut(colorize('Picked DirectTLS', 'blue'));
        } elseif ($results['starttls'] !== false && $results['starttls'][0]['target'] !== '.') {
            $host = $results['starttls'][0]['target'];
            $port = $results['starttls'][0]['port'];
            logOut(colorize('Picked STARTTLS', 'blue'));
        } else {
            // No SRV, we fallback to the default host
            $session = Session::start();
            $host = $session->get('host');
        }

        logOut(colorize('Connect to '.$host.':'.$port, 'blue'));
        $connector->connect($host.':'.$port)->then(
            $xmppBehaviour,
            function () {
                $evt = new Movim\Widget\Event;
                $evt->run('timeout_error');
                $this->cancel();
            }
        );
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
                // Set the host, useful for the CN certificate check
                $session = Session::start();

                // If the host is already set, we already launched the registration process
                if ($session->get('host')) {
                    return;
                } else {
                    $session->set('host', $msg->host);
                }

                global $loop;
                $results = [];

                Timer\timeout($dns->resolveAll('_xmpps-client._tcp.' . $msg->host, React\Dns\Model\Message::TYPE_SRV), 3.0, $loop)
                    ->then(
                        function ($resolved) use (&$results, &$dns, $connector, $xmppBehaviour) {
                            $results['directtls'] = $resolved;
                            handleClientDNS($results, $dns, $connector, $xmppBehaviour);
                        },
                        function ($rejected) use (&$results, &$dns, $connector, $xmppBehaviour) {
                            $results['directtls'] = false;
                            handleClientDNS($results, $dns, $connector, $xmppBehaviour);
                        }
                    );

                Timer\timeout($dns->resolveAll('_xmpp-client._tcp.' . $msg->host, React\Dns\Model\Message::TYPE_SRV), 3.0, $loop)
                    ->then(
                        function ($resolved) use (&$results, &$dns, $connector, $xmppBehaviour) {
                            $results['starttls'] = $resolved;
                            handleClientDNS($results, $dns, $connector, $xmppBehaviour);
                        },
                        function ($rejected) use (&$results, &$dns, $connector, $xmppBehaviour) {
                            $results['starttls'] = false;
                            handleClientDNS($results, $dns, $connector, $xmppBehaviour);
                        }
                    );

                break;
        }
    }

    return;
};

$xmppBehaviour = function (React\Socket\Connection $stream) use (&$xmppSocket, $parser, &$timestampReceive) {
    global $wsSocket;
    global $directTLSSocket;

    $xmppSocket = $stream;

    if ($directTLSSocket) {
        enableEncryption($xmppSocket->stream);
        stream_set_blocking($xmppSocket->stream, 0);
    }

    if (getenv('verbose')) {
        logOut(colorize('XMPP socket launched', 'blue'));
        logOut(" launched : ".\sizeToCleanSize(memory_get_usage()));
    }

    $xmppSocket->on('data', function ($message) use (&$xmppSocket, $parser, &$timestampReceive) {
        if (!empty($message)) {
            $restart = false;

            if (getenv('debug')) {
                logOut(colorize($message.' ', 'yellow') . colorize('received', 'green'));
            }

            if ($message == '</stream:stream>') {
                $xmppSocket->close();
                shutdown();
            } elseif ($message == "<proceed xmlns='urn:ietf:params:xml:ns:xmpp-tls'/>"
                  || $message == '<proceed xmlns="urn:ietf:params:xml:ns:xmpp-tls"/>') {
                $success = enableEncryption($xmppSocket->stream);
                if (!$success) return;

                if (getenv('verbose')) {
                    logOut(colorize('TLS enabled', 'blue'));
                }

                $restart = true;
            }

            $timestampReceive = time();

            if ($restart) {
                $session = Session::start();
                \Moxl\Stanza\Stream::init($session->get('host'));
                stream_set_blocking($xmppSocket->stream, 0);
                $restart = false;
            }

            if (!$parser->parse($message)) {
                logOut($parser->getError());
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
$wsConnector('ws://127.0.0.1:' . getenv('port'), [], [
    'MOVIM_SESSION_ID' => getenv('sid'),
    'MOVIM_DAEMON_KEY' => getenv('key')
])->then(function (Ratchet\Client\WebSocket $socket) use (&$wsSocket, $wsSocketBehaviour) {
    $wsSocket = $socket;
    $wsSocket->on('message', $wsSocketBehaviour);
});

$loop->run();
