<?php
require __DIR__ . '/vendor/autoload.php';

define('DOCUMENT_ROOT', dirname(__FILE__));

gc_enable();

use Movim\Bootstrap;

$bootstrap = new Bootstrap;
$booted = $bootstrap->boot();

$loop = React\EventLoop\Factory::create();

$connector = new React\SocketClient\TcpConnector($loop);
$stdin = new React\Stream\Stream(STDIN, $loop);

\Movim\Task\Engine::init($loop);

// We load and register all the widgets
$wrapper = \Movim\Widget\Wrapper::getInstance();
$wrapper->registerAll($bootstrap->getWidgets());


$conn = null;

$parser = new \Moxl\Parser;

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
    if($timestamp < time() - 3600*6) {
        $conn->close();
    }
});

// One connected ping each 5 mins
/*$loop->addPeriodicTimer(5*60, function() use (&$conn) {
    if(isset($conn)
    && is_resource($conn->stream)) {
        $ping = new \Moxl\Xec\Action\Ping\Server;
        $ping->request();

        $conn->write(trim(\Moxl\API::commit()));
        \Moxl\API::clear();
    }
});*/

function writeOut()
{
    $msg = \RPC::commit();

    if(!empty($msg)) {
        echo base64_encode(gzcompress(json_encode($msg), 9))."";
        //fwrite(STDERR, colorize(json_encode($msg).' '.strlen($msg), 'yellow')." : ".colorize('sent to browser', 'green')."\n");
    }

    \RPC::clear();
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

function tick()
{
    global $loop;
    $loop->tick();
}

$stdin_behaviour = function ($data) use (&$conn, $loop, &$buffer, &$connector, &$xmpp_behaviour, &$parser, &$timestamp)
{
    if(substr($data, -1) == "") {
        $messages = explode("", $buffer . substr($data, 0, -1));
        $buffer = '';

        foreach ($messages as $message) {
            #fwrite(STDERR, colorize($message, 'yellow')." : ".colorize('received from the browser', 'green')."\n");

            $msg = json_decode($message);

            if(isset($msg)) {
                switch ($msg->func) {
                    case 'message':
                        $msg = $msg->body;
                        break;

                    case 'ping':
                        // And we say that we are ready !
                        $obj = new \StdClass;
                        $obj->func = 'pong';
                        echo base64_encode(gzcompress(json_encode($obj), 9))."";
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
                        $loop->stop();
                        break;

                    case 'register':
                        if(isset($conn)
                        && is_resource($conn->stream)) {
                            $conn->stream->close();
                        }

                        $cd = new \Modl\ConfigDAO();
                        $config = $cd->get();

                        $port = 5222;
                        $dns = \Moxl\Utils::resolveHost($msg->host);
                        if(isset($dns->target) && $dns->target != null) $msg->host = $dns->target;
                        if(isset($dns->port) && $dns->port != null) $port = $dns->port;
                        #fwrite(STDERR, colorize('open a socket to '.$domain, 'yellow')." : ".colorize('sent to XMPP', 'green')."\n");

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

                        $connector->create($ip, $port)->then($xmpp_behaviour);
                        break;
                }

                $rpc = new \RPC();
                $rpc->handle_json($msg);

                writeOut();
            } else {
                return;
            }
        }
    } else {
        $buffer .= $data;
    }
};

$xmpp_behaviour = function (React\Stream\Stream $stream) use (&$conn, $loop, &$stdin, $stdin_behaviour, $parser, &$timestamp)
{
    $conn = $stream;

    if(getenv('verbose')) {
        fwrite(STDERR, colorize(getenv('sid'), 'yellow')." : ".colorize('linker launched', 'blue')."\n");
        fwrite(STDERR, colorize(getenv('sid'), 'yellow')." launched : ".\sizeToCleanSize(memory_get_usage())."\n");
    }

    $stdin->removeAllListeners('data');
    $stdin->on('data', $stdin_behaviour);

    // We define a huge buffer to prevent issues with SSL streams, see https://bugs.php.net/bug.php?id=65137
    $conn->bufferSize = 1024*32;
    //$conn->bufferSize = 1024;
    $conn->on('data', function($message) use (&$conn, $loop, $parser, &$timestamp) {
        if(!empty($message)) {
            $restart = false;

            if(getenv('debug')) {
                fwrite(STDERR, colorize($message, 'yellow')." : ".colorize('received', 'green')."\n");
            }

            if($message == '</stream:stream>') {
                $conn->close();
                $loop->stop();
            } elseif($message == "<proceed xmlns='urn:ietf:params:xml:ns:xmpp-tls'/>"
                  || $message == '<proceed xmlns="urn:ietf:params:xml:ns:xmpp-tls"/>') {
                $session = \Session::start();
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
                    $loop->stop();
                    return;
                }

                if(getenv('verbose')) {
                    fwrite(STDERR, colorize(getenv('sid'), 'yellow')." : ".colorize('TLS enabled', 'blue')."\n");
                }

                $restart = true;
            }

            #fwrite(STDERR, colorize(getenv('sid'), 'yellow')." widgets : ".\sizeToCleanSize(memory_get_usage())."\n");

            $timestamp = time();

            if($restart) {
                $session = \Session::start();
                \Moxl\Stanza\Stream::init($session->get('host'));
                stream_set_blocking($conn->stream, 0);
                $restart = false;
            }

            if(!$parser->parse($message)) {
                fwrite(STDERR, colorize(getenv('sid'), 'yellow')." ".$parser->getError()."\n");
            }

            while($parser->nodes && !$parser->nodes->isEmpty()) {
                $node = $parser->nodes->dequeue();
                \Moxl\Xec\Handler::handle($node);

                $loop->tick();

                unset($node);
            }

            writeOut();

            //fwrite(STDERR, colorize(getenv('sid'), 'yellow')." end data : ".\sizeToCleanSize(memory_get_usage())."\n");
        }

        $loop->tick();
    });

    $conn->on('error', function($msg) use ($conn, $loop) {
        #fwrite(STDERR, colorize(serialize($msg), 'red')." : ".colorize('error', 'green')."\n");
        $loop->stop();
    });

    $conn->on('close', function($msg) use ($conn, $loop) {
        #fwrite(STDERR, colorize(serialize($msg), 'red')." : ".colorize('closed', 'green')."\n");
        $loop->stop();
    });

    // And we say that we are ready !
    $obj = new \StdClass;
    $obj->func = 'registered';

    fwrite(STDERR, 'registered');

    //fwrite(STDERR, colorize(json_encode($obj).' '.strlen($obj), 'yellow')." : ".colorize('obj sent to browser', 'green')."\n");

    echo base64_encode(gzcompress(json_encode($obj), 9))."";
};

$stdin->on('data', $stdin_behaviour);
$stdin->on('error', function() use($loop) { $loop->stop(); } );
$stdin->on('close', function() use($loop) { $loop->stop(); } );

$loop->run();
