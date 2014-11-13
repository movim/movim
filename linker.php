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

$cd = new \Modl\ConfigDAO();
$config = $cd->get();

//setcookie('PHPENV', getenv('sid'), time()+3600);
/*
$connector_xmpp = new React\SocketClient\Connector($loop, $dns);
$secure_connector_xmpp = new React\SocketClient\SecureConnector($connector_xmpp, $loop);
* $secure_connector_xmpp->create('movim.eu', 5222)*/

React\Promise\all([$connector('ws://127.0.0.1:8080'), $connector($config->websocketurl, ['xmpp'])])->then(function($conns) use ($loop) {
    list($conn1, $conn2) = $conns;

    $logger = new \Zend\Log\Logger();
    $writer = new \Zend\Log\Writer\Syslog(array('application' => 'movim_daemon'));
    $logger->addWriter($writer);

    $conn1->on('message', function($msg) use ($conn1, $logger, $conn2) {
        if($msg != '') {
            $rpc = new RPC();
            $rpc->handle_json($msg);
            //$logger->notice("LOOP : Got message {$msg}");

            $xml = \Moxl\API::commit();
            \Moxl\API::clear();

            if(!empty($xml)) {
                $logger->notice("LOOP : Send to XMPP {$xml}");
            
                $conn2->send(trim($xml));
            }

            $obj = new \StdClass;
            $obj->func = 'message';
            $obj->body = RPC::commit();
            RPC::clear();

            if(!empty($obj->body)) {
                $conn1->send(json_encode($obj));
            }
        }
    });
    
    $conn2->on('message', function($msg) use ($conn1, $logger, $conn2, $loop) {
        $logger->notice("XMPP : Got message from XMPP {$msg}");

        if($msg == '</stream:stream>') {
            $conn2->close();
            $conn1->close();
            $loop->stop();
        }

        \Moxl\API::clear();
        \RPC::clear();

        \Moxl\Xec\Handler::handleStanza($msg);

        $xml = \Moxl\API::commit();
        \Moxl\API::clear();

        if(!empty($xml)) {
            $logger->notice("XMPP : Send to XMPP {$xml}");
        
            $conn2->send(trim($xml));
        }

        $obj = new \StdClass;
        $obj->func = 'message';
        $obj->body = \RPC::commit();
        $out = json_encode($obj->body);
        $logger->notice("XMPP : Send to LOOP {$out}");
        \RPC::clear();

        if(!empty($obj->body)) {
            $conn1->send(json_encode($obj));
        }
    });
    
    $conn2->on('error', function($msg) use ($logger) {
        $logger->notice("XMPP : Got error {$msg}");
        $conn1->close();
        $loop->stop();
    });
    
    $conn2->on('close', function($msg) use ($logger) {
        $logger->notice("XMPP : Got close");
        if($conn1 != null) $conn1->close();
        if($loop  != null) $loop->stop();
    });

    $obj = new \StdClass;
    $obj->func = 'register_linker';
    $obj->sid  = getenv('sid');

    $conn1->send(json_encode($obj));

}, function($e) {
    $logger = new \Zend\Log\Logger();
    $writer = new \Zend\Log\Writer\Syslog(array('application' => 'movim_daemon'));
    $logger->addWriter($writer);
    
    $logger->notice("LOOP : Error {$e->getMessage()}");
});

$loop->run();
