<?php
require __DIR__ . '/vendor/autoload.php';

define('DOCUMENT_ROOT', dirname(__FILE__));
require_once(DOCUMENT_ROOT.'/bootstrap.php');

$bootstrap = new Bootstrap();
$booted = $bootstrap->boot();

$loop = React\EventLoop\Factory::create();

$dnsResolverFactory = new React\Dns\Resolver\Factory();
$dns = $dnsResolverFactory->createCached('8.8.8.8', $loop);

$connector = new Ratchet\Client\Factory($loop);

$stdin = new React\Stream\Stream(STDIN, $loop);

$cd = new \Modl\ConfigDAO();
$config = $cd->get();

$connector($config->websocketurl, array('xmpp'))->then(function($conn) use (&$stdin, $loop) {
    fwrite(STDERR, colorize(getenv('sid'), 'yellow')." : ".colorize('linker launched', 'blue')."\n");
    
    $conn->on('message', function($message) use ($conn, $loop) {
        if($message != '') {
            //fwrite(STDERR, colorize($message, 'yellow')." : ".colorize('received', 'green')."\n");

            if($message == '</stream:stream>') {
                $conn->close();
                $loop->stop();
            }

            \Moxl\API::clear();
            \RPC::clear();

            \Moxl\Xec\Handler::handleStanza($message);

            $xml = \Moxl\API::commit();
            \Moxl\API::clear();

            $msg = \RPC::commit();
            \RPC::clear();

            if(!empty($msg)) {
                $msg = json_encode($msg);
                //fwrite(STDERR, colorize($msg, 'yellow')." : ".colorize('sent to browser', 'green')."\n");
                echo base64_encode(gzcompress($msg, 9))."END";
            }

            if(!empty($xml)) {
                //fwrite(STDERR, colorize(trim($xml), 'yellow')." : ".colorize('sent to XMPP', 'green')."\n");
                $conn->send(trim($xml));
            }
        }
    });

    $conn->on('error', function($msg) use ($conn, $loop) {
        $loop->stop();
    });

    $conn->on('close', function($msg) use ($conn, $loop) {
        $loop->stop();
    });

    $stdin->removeAllListeners('data');
    $stdin->on('data', function ($data) use ($conn, $loop) {
        $messages = explode("\n", trim($data));
        foreach ($messages as $message) {
            //fwrite(STDERR, colorize($message, 'yellow')." : ".colorize('received from the browser', 'green')."\n");
            
            $msg = json_decode($message);

            if($msg->func == 'message' && $msg->body != '') {
                $msg = $msg->body;
            } elseif($msg->func == 'unregister') {
                $conn->close();
                $loop->stop();
            } else {
                return;
            }
            
            $rpc = new \RPC();
            $rpc->handle_json($msg);

            $xml = \Moxl\API::commit();
            \Moxl\API::clear();
            
            if(!empty($xml)) {
                //fwrite(STDERR, colorize(trim($xml), 'yellow')." : ".colorize('sent to XMPP', 'green')."\n");
                $conn->send(trim($xml));
            }

            $msg = json_encode(\RPC::commit());
            \RPC::clear();

            if(!empty($msg)) {
                echo base64_encode(gzcompress($msg, 9))."END";
            }
        }
    });

    // And we say that we are ready !
    $obj = new \StdClass;
    $obj->func = 'registered';

    echo base64_encode(gzcompress(json_encode($obj), 9))."END";
});

// Fallback event, when the WebSocket is not enabled,
// we still handle browser to Movim requests
$stdin->on('data', function ($data) use ($loop) {
    $messages = explode("\n", trim($data));
    foreach ($messages as $message) {
        //fwrite(STDERR, colorize($message, 'yellow')." : ".colorize('received from the browser', 'green')."\n");
        
        $msg = json_decode($message);

        if($msg->func == 'message' && $msg->body != '') {
            $msg = $msg->body;
        } elseif($msg->func == 'unregister') {
            $loop->stop();
        } else {
            return;
        }
        
        $rpc = new \RPC();
        $rpc->handle_json($msg);

        $msg = json_encode(\RPC::commit());
        \RPC::clear();

        if(!empty($msg)) {
            echo base64_encode(gzcompress($msg, 9))."END";
        }
    }
});

$loop->run();
