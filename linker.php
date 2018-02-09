<?php
require __DIR__ . '/vendor/autoload.php';

define('DOCUMENT_ROOT', dirname(__FILE__));

gc_enable();

use Movim\Bootstrap;
use Movim\RPC;
use Movim\Session;

$bootstrap = new Bootstrap;
$bootstrap->boot();

$loop = React\EventLoop\Factory::create();

$connector = new React\Socket\TcpConnector($loop);
$stdin = new React\Stream\ReadableResourceStream(STDIN, $loop);

// We load and register all the widgets
$wrapper = \Movim\Widget\Wrapper::getInstance();
$wrapper->registerAll($bootstrap->getWidgets());

$conn = null;

$parser = new \Moxl\Parser(function ($node) {
    \Moxl\Xec\Handler::handle($node);
});

$buffer = '';

$timestamp = time();

function handleSSLErrors($errno, $errstr) {
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
$loop->addPeriodicTimer(5, function() use(&$conn, &$timestamp) {
    if($timestamp < time() - 3600*4
    && isset($conn)) {
        $conn->close();
    }
});

$zmq = new \React\ZMQ\Context($loop, new \ZMQContext(2, false));
$file = CACHE_PATH . 'movim_feeds_' . getenv('sid') . '.ipc';

$pullSocket = $zmq->getSocket(ZMQ::SOCKET_PUSH);
$pullSocket->getWrappedSocket()->setSockOpt(\ZMQ::SOCKOPT_LINGER, 0);
$pullSocket->connect('ipc://' . $file . '_pull', true);

$pushSocket = $zmq->getSocket(ZMQ::SOCKET_PULL);
$pushSocket->getWrappedSocket()->setSockOpt(\ZMQ::SOCKOPT_LINGER, 0);
$pushSocket->connect('ipc://'.$file . '_push', true);

function writeOut($msg = null)
{
    global $pullSocket;

    if(!empty($msg)) {
        $pullSocket->send(json_encode($msg));
    }
}

function writeXMPP($xml)
{
    global $conn;

    if(!empty($xml) && $conn) {
        $conn->write(trim($xml));

        if(getenv('debug')) {
            fwrite(STDERR, colorize(trim($xml), 'yellow')." : ".colorize('sent to XMPP', 'green')."\n");
        }
    }
}

function shutdown()
{
    global $pullSocket;
    global $pushSocket;
    global $loop;

    $pullSocket->close();
    $pushSocket->close();

    $loop->stop();
}

$pushSocketBehaviour = function ($msg) use (&$conn, $loop, &$buffer, &$connector, &$xmppBehaviour)
{
    global $pullSocket;

    $msg = json_decode($msg);

    if(isset($msg)) {
        switch ($msg->func) {
            case 'message':
                (new RPC)->handleJSON($msg->body);
                break;

            case 'ping':
                // And we say that we are ready !
                $obj = new \StdClass;
                $obj->func = 'pong';
                $pullSocket->send(json_encode($obj));
                break;

            case 'down':
                if(isset($conn)
                && is_resource($conn->stream)) {
                    $evt = new Movim\Widget\Event;
                    $evt->run('session_down');
                }
                break;

            case 'up':
                if(isset($conn)
                && is_resource($conn->stream)) {
                    $evt = new Movim\Widget\Event;
                    $evt->run('session_up');
                }
                break;

            case 'unregister':
                \Moxl\Stanza\Stream::end();
                if(isset($conn)) $conn->close();
                shutdown();
                break;

            case 'register':
                $cd = new \Modl\ConfigDAO;
                $config = $cd->get();

                $port = 5222;
                $dns = \Moxl\Utils::resolveHost($msg->host);
                if(isset($dns->target) && $dns->target != null) $msg->host = $dns->target;
                if(isset($dns->port) && $dns->port != null) $port = $dns->port;

                $ip = \Moxl\Utils::resolveIp($msg->host);
                $ip = (!$ip || !isset($ip->address)) ? gethostbyname($msg->host) : $ip->address;

                if(getenv('verbose')) {
                    fwrite(
                        STDERR,
                        colorize(
                            getenv('sid'), 'yellow')." : ".
                            colorize('Connection to '.$msg->host.' ('.$ip.')', 'blue').
                            "\n");
                }

                $connector->connect('['.$ip.']:'.$port)->then($xmppBehaviour);
                break;
        }
    } else {
        return;
    }
};

$xmppBehaviour = function (React\Socket\Connection $stream) use (&$conn, $loop, &$stdin, $pushSocketBehaviour, $parser, &$timestamp)
{
    global $pullSocket;

    $conn = $stream;

    if(getenv('verbose')) {
        fwrite(STDERR, colorize(getenv('sid'), 'yellow')." : ".colorize('linker launched', 'blue')."\n");
        fwrite(STDERR, colorize(getenv('sid'), 'yellow')." launched : ".\sizeToCleanSize(memory_get_usage())."\n");
    }

    // We define a huge buffer to prevent issues with SSL streams, see https://bugs.php.net/bug.php?id=65137
    $conn->on('data', function($message) use (&$conn, $loop, $parser, &$timestamp) {
        if(!empty($message)) {
            $restart = false;

            if(getenv('debug')) {
                fwrite(STDERR, colorize($message, 'yellow')." : ".colorize('received', 'green')."\n");
            }

            if($message == '</stream:stream>') {
                $conn->close();
                shutdown();
            } elseif($message == "<proceed xmlns='urn:ietf:params:xml:ns:xmpp-tls'/>"
                  || $message == '<proceed xmlns="urn:ietf:params:xml:ns:xmpp-tls"/>') {
                $session = Session::start();
                stream_set_blocking($conn->stream, 1);
                stream_context_set_option($conn->stream, 'ssl', 'SNI_enabled', false);
                stream_context_set_option($conn->stream, 'ssl', 'peer_name', $session->get('host'));
                stream_context_set_option($conn->stream, 'ssl', 'allow_self_signed', true);

                // See http://php.net/manual/en/function.stream-socket-enable-crypto.php#119122
                $crypto_method = STREAM_CRYPTO_METHOD_TLS_CLIENT;

                if (defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')) {
                    $crypto_method |= STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
                    $crypto_method |= STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT;
                }

                set_error_handler('handleSSLErrors');
                $out = stream_socket_enable_crypto($conn->stream, 1, $crypto_method);
                restore_error_handler();

                if($out !== true) {
                    $evt = new Movim\Widget\Event;
                    $evt->run('ssl_error');

                    shutdown();
                    return;
                }

                if(getenv('verbose')) {
                    fwrite(STDERR, colorize(getenv('sid'), 'yellow')." : ".colorize('TLS enabled', 'blue')."\n");
                }

                $restart = true;
            }

            $timestamp = time();

            if($restart) {
                $session = Session::start();
                \Moxl\Stanza\Stream::init($session->get('host'));
                stream_set_blocking($conn->stream, 0);
                $restart = false;
            }

            if(!$parser->parse($message)) {
                fwrite(STDERR, colorize(getenv('sid'), 'yellow')." ".$parser->getError()."\n");
            }
        }
    });

    $conn->on('error', function() use ($conn, $loop) {
        shutdown();
    });

    $conn->on('close', function() use ($conn, $loop) {
        shutdown();
    });

    // And we say that we are ready !
    $obj = new \StdClass;
    $obj->func = 'registered';

    fwrite(STDERR, 'registered');

    $pullSocket->send(json_encode($obj));
};

$pushSocket->on('message', $pushSocketBehaviour);

$stdin->on('error', function() use($loop) { shutdown(); } );
$stdin->on('close', function() use($loop) { shutdown(); } );

$loop->run();
