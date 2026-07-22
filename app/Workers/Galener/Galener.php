<?php

namespace App\Workers\Galener;

use Moxl\Parser;
use React\Dns\Resolver\ResolverInterface;
use React\Socket\Connection;
use React\Socket\Connector;
use React\Socket\HappyEyeBallsConnector;
use Moxl\Stanza\Stream;
use App\Workers\Galener\XMPPHandler;

class Galener
{
    private ResolverInterface $dns;
    private Parser $parser;
    private Connection $connection;
    private XMPPHandler $handler;
    private int $galeneHttpPort = 8444;
    private string $galeneHttpAdminUsername = 'admin';
    private string $galeneHttpAdminPassword = '123';
    private string $host = 'sfu.movim.eu';
    private int $port = 5353;
    private string $password = 'galener';

    public function __construct()
    {
        /*$process = new \React\ChildProcess\Process(
            'cd ' . DOCUMENT_ROOT . '/galene && ./galene -insecure -http :' . $this->galeneHttpPort . ' -data ' . DOCUMENT_ROOT . '/galene/data/'
        );

        $process->stdout?->on('data', function ($chunk) {
            \logError('galener' . $chunk);
        });

        $process->stdout?->on('error', function (\Exception $e) {
            \logError('galener' . $e->getMessage());
        });

        $process->on('exit', function ($exitCode, $termSignal) {
            echo 'Process exited with code ' . $exitCode . PHP_EOL;
        });

        $process->start();*/

        $galeneAPIClient = new GaleneAPIClient(
            port: $this->galeneHttpPort,
            adminUsername: $this->galeneHttpAdminUsername,
            adminPassword: $this->galeneHttpAdminPassword
        );

        $conferencesManager = new ConferencesManager(
            apiClient: $galeneAPIClient,
            sendXMPP: fn(?\DOMDocument $dom = null) => $this->sendXMPP($dom)
        );

        $config = \React\Dns\Config\Config::loadSystemConfigBlocking();
        $server = $config->nameservers ? reset($config->nameservers) : '8.8.8.8';

        $factory = new \React\Dns\Resolver\Factory();
        $this->dns = $factory->create($server);

        $this->handler = (new XMPPHandler(
            apiClient: $galeneAPIClient,
            conferencesManager: $conferencesManager
        ));
        $this->parser = new Parser(
            fn(\SimpleXMLElement $node) => $this->sendXMPP(
                $this->handler->handle($node)
            )
        );

        $conferencesManager->createConference('test');

        $this->registerXMPP();
    }

    public function registerXMPP()
    {
        $connector = new HappyEyeBallsConnector(
            null,
            new Connector([
                'timeout' => 5.0,
                'tls' => [
                    'SNI_enabled' => true,
                    'allow_self_signed' => false,
                    'peer_name' => $this->host
                ]
            ]),
            $this->dns
        );

        $connector->connect($this->host . ':' . $this->port)->then(
            fn($connection) => $this->xmppBehaviour($connection),
            function (\Exception $error) {
                \logDebug($error->getMessage());
            }
        );
    }

    private function sendXMPP(?\DOMDocument $dom = null)
    {
        if ($dom) {
            \logDebug('SEND >>>> ' . $dom->saveXML($dom->documentElement));
            $this->connection->write(trim($dom->saveXML($dom->documentElement)));
        }
    }

    private function xmppBehaviour(Connection $connection)
    {
        $this->connection = $connection;
        $this->connection->on('data', function ($message) {
            if (str_starts_with($message, "<?xml version='1.0'?><stream:stream")) {
                if ($stream = simplexml_load_string($message . '</stream:stream>')) {
                    $this->connection->write(Stream::initComponentHandshake(sid: (string)$stream->attributes()->id, password: $this->password));
                }
            }

            if (!$this->parser->parse($message)) {
                \logDebug($this->parser->getError());
            }
        });

        $this->connection->write(Stream::initComponent($this->host));
    }
}
