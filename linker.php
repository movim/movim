<?php
require __DIR__ . '/vendor/autoload.php';

define('DOCUMENT_ROOT', dirname(__FILE__));
require_once(DOCUMENT_ROOT.'/bootstrap.php');

gc_enable();

$bootstrap = new Bootstrap();
$booted = $bootstrap->boot();

//fwrite(STDERR, colorize(getenv('sid'), 'yellow')." booted : ".\sizeToCleanSize(memory_get_usage())."\n");

$loop = React\EventLoop\Factory::create();

//fwrite(STDERR, colorize(getenv('sid'), 'yellow')." loop : ".\sizeToCleanSize(memory_get_usage())."\n");

/*$dnsResolverFactory = new React\Dns\Resolver\Factory();
$dns = $dnsResolverFactory->createCached('8.8.8.8', $loop);*/

$connector = new Ratchet\Client\Factory($loop);

//fwrite(STDERR, colorize(getenv('sid'), 'yellow')." connector : ".\sizeToCleanSize(memory_get_usage())."\n");

$stdin = new React\Stream\Stream(STDIN, $loop);

//fwrite(STDERR, colorize(getenv('sid'), 'yellow')." stdin : ".\sizeToCleanSize(memory_get_usage())."\n");

$cd = new \Modl\ConfigDAO();
$config = $cd->get();

//fwrite(STDERR, colorize(getenv('sid'), 'yellow')." config : ".\sizeToCleanSize(memory_get_usage())."\n");

fwrite(STDERR, colorize(getenv('sid'), 'yellow')." widgets before : ".\sizeToCleanSize(memory_get_usage())."\n");

// We load and register all the widgets
$wrapper = WidgetWrapper::getInstance();
$wrapper->registerAll(true);

fwrite(STDERR, colorize(getenv('sid'), 'yellow')." widgets : ".\sizeToCleanSize(memory_get_usage())."\n");

$connector($config->websocketurl, array('xmpp'))->then(function($conn) use (&$stdin, $loop) {
    fwrite(STDERR, colorize(getenv('sid'), 'yellow')." : ".colorize('linker launched', 'blue')."\n");
    fwrite(STDERR, colorize(getenv('sid'), 'yellow')." launched : ".\sizeToCleanSize(memory_get_usage())."\n");
    
    $conn->on('message', function($message) use ($conn, $loop) {
        if($message != '') {
            //fwrite(STDERR, colorize($message, 'yellow')." : ".colorize('received', 'green')."\n");

            if($message == '</stream:stream>') {
                $conn->close();
                $loop->stop();
            }

            \Moxl\API::clear();
            \RPC::clear();

            //fwrite(STDERR, colorize(getenv('sid'), 'yellow')." before handle : ".\sizeToCleanSize(memory_get_usage())."\n");

            \Moxl\Xec\Handler::handleStanza($message);

            //fwrite(STDERR, colorize(getenv('sid'), 'yellow')." after handle : ".\sizeToCleanSize(memory_get_usage())."\n");

            $xml = \Moxl\API::commit();
            \Moxl\API::clear();

            //fwrite(STDERR, colorize(getenv('sid'), 'yellow')." after commit : ".\sizeToCleanSize(memory_get_usage())."\n");

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
    $stdin->on('data', function ($data) use ($conn, $loop, &$buffer) {
        // A little bit of signalisation to use properly the buffer
        if(substr($data, -3) == "END") {
            $messages = explode("END", $buffer . substr($data, 0, -3));
            $buffer = '';

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
                    //fwrite(STDERR, colorize($msg, 'yellow')." : ".colorize('sent to the browser', 'green')."\n");
                    echo base64_encode(gzcompress($msg, 9))."END";
                }
            }
        } else {
            $buffer .= $data;
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

        if(isset($msg)) {
            if($msg->func == 'message' && $msg->body != '') {
                $msg = $msg->body;
            } elseif($msg->func == 'unregister') {
                $loop->stop();
            }
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
