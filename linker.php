<?php
require __DIR__ . '/vendor/autoload.php';

define('DOCUMENT_ROOT', dirname(__FILE__));
require_once(DOCUMENT_ROOT.'/bootstrap.php');

gc_enable();

$bootstrap = new Bootstrap();
$booted = $bootstrap->boot();

$loop = React\EventLoop\Factory::create();

$dnsResolverFactory = new React\Dns\Resolver\Factory();
$dns = $dnsResolverFactory->createCached('8.8.8.8', $loop);

$connector = new React\SocketClient\Connector($loop, $dns);
$stdin = new React\Stream\Stream(STDIN, $loop);

fwrite(STDERR, colorize(getenv('sid'), 'yellow')." widgets before : ".\sizeToCleanSize(memory_get_usage())."\n");

// We load and register all the widgets
$wrapper = WidgetWrapper::getInstance();
$wrapper->registerAll(true);

fwrite(STDERR, colorize(getenv('sid'), 'yellow')." widgets : ".\sizeToCleanSize(memory_get_usage())."\n");

$conn = null;

$parser = new \Moxl\Parser;

$buffer = '';

$stdin_behaviour = function ($data) use (&$conn, $loop, &$buffer, &$connector, &$xmpp_behaviour, &$parser) {
    if(substr($data, -1) == "") {
        $messages = explode("", $buffer . substr($data, 0, -1));
        $buffer = '';

        foreach ($messages as $message) {
            #fwrite(STDERR, colorize($message, 'yellow')." : ".colorize('received from the browser', 'green')."\n");

            $msg = json_decode($message);

            if(isset($msg)) {
                if($msg->func == 'message' && $msg->body != '') {
                    $msg = $msg->body;
                } elseif($msg->func == 'unregister') {
                    \Moxl\Stanza\Stream::end();
                } elseif($msg->func == 'register') {
                    $cd = new \Modl\ConfigDAO();
                    $config = $cd->get();

                    $port = 5222;

                    $dns = \Moxl\Utils::resolveHost($msg->host);
                    if(isset($dns[0]['target']) && $dns[0]['target'] != null) $msg->host = $dns[0]['target'];
                    if(isset($dns[0]['port']) && $dns[0]['port'] != null) $port = $dns[0]['port'];
                    #fwrite(STDERR, colorize('open a socket to '.$domain, 'yellow')." : ".colorize('sent to XMPP', 'green')."\n");
                    $connector->create($msg->host, $port)->then($xmpp_behaviour);
                }
            } else {
                return;
            }

            $rpc = new \RPC();
            $rpc->handle_json($msg);

            $msg = \RPC::commit();
            \RPC::clear();

            if(!empty($msg)) {
                //echo json_encode($msg)."";
                echo base64_encode(gzcompress(json_encode($msg), 9))."";
                //fwrite(STDERR, colorize(json_encode($msg), 'yellow')." : ".colorize('sent to the browser', 'green')."\n");
            }

            $xml = \Moxl\API::commit();
            \Moxl\API::clear();

            //$loop->tick();

            if(!empty($xml) && $conn) {
                $conn->write(trim($xml));
                #fwrite(STDERR, colorize(trim($xml), 'yellow')." : ".colorize('sent to XMPP', 'green')."\n");
            }
        }
    } else {
        $buffer .= $data;
    }

    //$loop->tick();
};

$xmpp_behaviour = function (React\Stream\Stream $stream) use (&$conn, $loop, &$stdin, $stdin_behaviour, $parser) {
    $conn = $stream;
    fwrite(STDERR, colorize(getenv('sid'), 'yellow')." : ".colorize('linker launched', 'blue')."\n");
    fwrite(STDERR, colorize(getenv('sid'), 'yellow')." launched : ".\sizeToCleanSize(memory_get_usage())."\n");

    $stdin->removeAllListeners('data');
    $stdin->on('data', $stdin_behaviour);

    // We define a huge buffer to prevent issues with SSL streams, see https://bugs.php.net/bug.php?id=65137
    $conn->bufferSize = 1024*32;
    $conn->on('data', function($message) use (&$conn, $loop, $parser) {
        if(!empty($message)) {
            $restart = false;

            if($message == '</stream:stream>') {
                $conn->close();
                $loop->stop();
            } elseif($message == "<proceed xmlns='urn:ietf:params:xml:ns:xmpp-tls'/>"
                  || $message == '<proceed xmlns="urn:ietf:params:xml:ns:xmpp-tls"/>') {
                stream_set_blocking($conn->stream, 1);
                $out = stream_socket_enable_crypto($conn->stream, 1, STREAM_CRYPTO_METHOD_TLS_CLIENT);

                $restart = true;
            }

            #fwrite(STDERR, colorize($message, 'yellow')." : ".colorize('received', 'green')."\n");
            #fwrite(STDERR, colorize(getenv('sid'), 'yellow')." widgets : ".\sizeToCleanSize(memory_get_usage())."\n");

            \Moxl\API::clear();
            \RPC::clear();

            if(!$parser->parse($message)) {
                fwrite(STDERR, colorize(getenv('sid'), 'yellow')." ".$parser->getError()."\n");
            }

            if($restart) {
                $session = \Sessionx::start();
                \Moxl\Stanza\Stream::init($session->host);
                stream_set_blocking($conn->stream, 0);
                $restart = false;
            }

            $msg = \RPC::commit();
            \RPC::clear();

            if(!empty($msg)) {
                //[MaJ[MaJ[MaJ[MaI[MaI[MaI[MaI[MaI[MaI[MaI[MaI[MaIecho json_encode($msg)."";
                echo base64_encode(gzcompress(json_encode($msg), 9))."";
                //fwrite(STDERR, colorize(json_encode($msg).' '.strlen($msg), 'yellow')." : ".colorize('sent to browser', 'green')."\n");
            }

            $xml = \Moxl\API::commit();
            \Moxl\API::clear();

            if(!empty($xml)) {
                $conn->write(trim($xml));
                #fwrite(STDERR, colorize(trim($xml), 'yellow')." : ".colorize('sent to XMPP', 'green')."\n");
            }
        }

        // Two ticks to be sure that we get everything from the socket, sic…
        $loop->tick();
        //$loop->tick();
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

    //echo json_encode($obj)."";
    //fwrite(STDERR, colorize(json_encode($obj).' '.strlen($obj), 'yellow')." : ".colorize('obj sent to browser', 'green')."\n");

    echo base64_encode(gzcompress(json_encode($obj), 9))."";
};

$stdin->on('data', $stdin_behaviour);
$stdin->on('error', function() use($loop) { $loop->stop(); } );
$stdin->on('close', function() use($loop) { $loop->stop(); } );

$loop->run();
