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
    public const CONFERENCES_CACHE = DOCUMENT_ROOT . '/' . CACHE_DIR . 'galener/config/';
    private const DATA_CACHE = DOCUMENT_ROOT . '/' . CACHE_DIR . 'galener/data/';
    private int $galeneHttpPort = 18444;
    private string $galeneHttpAdminUsername;
    private string $galeneHttpAdminPassword;
    private string $xmppHost;
    private string $xmppPassword;

    public function __construct()
    {
        // Generate a fresh admin
        $this->galeneHttpAdminUsername = 'movim_admin';
        $this->galeneHttpAdminPassword = generateKey(32);

        // Create the directories
        $galenerCache = DOCUMENT_ROOT . '/' . CACHE_DIR . 'galener/';

        if (!file_exists($galenerCache)) {
            mkdir($galenerCache);
            mkdir(self::CONFERENCES_CACHE);
            mkdir(self::DATA_CACHE);
        }

        // Set a fresh administration configuration

        $config = [
            'writableGroups' => true,
            'users' => [
                $this->galeneHttpAdminUsername => [
                    'password' => [
                        'type' => 'bcrypt',
                        'key' => password_hash($this->galeneHttpAdminPassword, PASSWORD_BCRYPT)
                    ],
                    'permissions' => 'admin'
                ]
            ]
        ];

        file_put_contents(self::DATA_CACHE . 'config.json', json_encode($config));

        // Launch galene

        $ex = config('galener.galene_path') .
            ' -insecure -http localhost:' . $this->galeneHttpPort .
            ' -data ' . self::DATA_CACHE .
            ' -groups ' . self::CONFERENCES_CACHE .
            ' -static ' . substr(config('galener.galene_path'), 0, -6) . 'static/';

        $process = new \React\ChildProcess\Process(
            $ex
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

        $process->start();

        $this->xmppHost = config('galener.xmpp_host');
        $this->xmppPassword = config('galener.xmpp_password');

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
                    'peer_name' => $this->xmppHost
                ]
            ]),
            $this->dns
        );

        $connector->connect($this->xmppHost . ':' . config('galener.xmpp_port'))->then(
            fn($connection) => $this->xmppBehaviour($connection),
            function (\Exception $error) {
                \logError($error->getMessage());
            }
        );
    }

    private function sendXMPP(?\DOMDocument $dom = null)
    {
        if ($dom) {
            $this->connection->write(trim($dom->saveXML($dom->documentElement)));
        }
    }

    private function xmppBehaviour(Connection $connection)
    {
        $this->connection = $connection;
        $this->connection->on('data', function ($message) {
            if (str_starts_with($message, "<?xml version='1.0'?><stream:stream")) {
                if ($stream = simplexml_load_string($message . '</stream:stream>')) {
                    $this->connection->write(Stream::initComponentHandshake(sid: (string)$stream->attributes()->id, password: $this->xmppPassword));
                }
            }

            if (!$this->parser->parse($message)) {
                \logError('Galener XMPP parser: ' . $this->parser->getError());
            }
        });

        $this->connection->write(Stream::initComponent($this->xmppHost));
    }
}
