<?php
require __DIR__ . '/vendor/autoload.php';

define('DOCUMENT_ROOT', dirname(__FILE__));
require_once(DOCUMENT_ROOT.'/bootstrap.php');

$bootstrap = new Bootstrap();
$booted = $bootstrap->boot();

set_time_limit(200);

$polling = true;

$loop = React\EventLoop\Factory::create();

$logger = new \Zend\Log\Logger();
$writer = new \Zend\Log\Writer\Syslog(array('application' => 'movim'));
$logger->addWriter($writer);

$client_xmpp = new \Devristo\Phpws\Client\WebSocket("ws://movim.eu:5290/", $loop, $logger);
$client_local = new \Devristo\Phpws\Client\WebSocket("ws://127.0.0.1:8080/", $loop, $logger);

// CLIENT 1

$client_xmpp->on("request", function($headers) use ($logger){
    $logger->notice("XMPP : Request object created!");
});

$client_xmpp->on("handshake", function() use ($logger) {
    $logger->notice("XMPP : Handshake received!");
});

$client_xmpp->on("connect", function($headers = false) use ($client_xmpp, $logger) {
    $logger->notice("XMPP : Connected");
});

$client_xmpp->on("disconnect", function($headers = false) use ($client_xmpp) {
    $logger->notice("XMPP : Disconnected");

    $client_local->close();
});

$client_xmpp->on("message", function($message) use ($client_local, $logger){
    $logger->notice("XMPP : Got message {$message->getData()}");

    $obj = new \StdClass;
    $obj->func = 'message';
    $obj->body = $message->getData();
    
    $client_local->send(json_encode($obj));
});

// CLIENT 2

$client_local->on("request", function($headers) use ($logger){
    $logger->notice("LOOP : Request object created!");
});

$client_local->on("handshake", function() use ($logger) {
    $logger->notice("LOOP : Handshake received!");
});

$client_local->on("connect", function($headers = false) use ($client_local, $logger) {
    $logger->notice("LOOP : Connected!");

    $obj = new \StdClass;
    $obj->func = 'register_linker';
    $obj->sid  = getenv('sid');

    $client_local->send(json_encode($obj));
});

$client_local->on("disconnect", function($headers = false) use ($client_local) {
    $logger->notice("LOOP : Disconnected");

    $client_xmpp->close();
});

$client_local->on("message", function($message) use ($client_local, $client_xmpp, $logger){

    if($message->getData() != '') {
        $rpc = new RPC();
        $rpc->handle_json($message->getData());
        $logger->notice("LOOP : Got message {$message->getData()}");

        $xml = \Moxl\API::commit();
        \Moxl\API::clear();

        if(!empty($xml)) {
            $logger->notice("LOOP : Send to XMPP {$xml}");
        
            $client_xmpp->send($xml);
        }

        $obj = new \StdClass;
        $obj->func = 'message';
        $obj->body = RPC::commit();
        RPC::clear();

        if(!empty($obj->body)) {
            $logger->notice("LOOP : Send to local {$obj->body}");
            
            $client_local->send(json_encode($obj));
        }
    }
});

$client_local->open();
$client_xmpp->open();

$loop->run();
