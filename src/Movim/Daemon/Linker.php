<?php

namespace Movim\Daemon;

use App\User;
use Movim\RPC;
use Movim\Session;
use Movim\Widget\Wrapper;
use Moxl\Parser;
use Ratchet\Client\WebSocket;
use React\Dns\Model\Message;
use React\Dns\Resolver\ResolverInterface;
use React\Socket\Connection;
use React\Socket\Connector;
use React\Socket\HappyEyeBallsConnector;

use function React\Promise\Timer\timeout;

class Linker
{
    private Parser $parser;
    private ?HappyEyeBallsConnector $connector = null;
    private ?Connection $connection = null;
    private ?string $host = null;
    private ?WebSocket $websocket = null;
    public ?User $user = null;

    public function __construct(
        private string $sessionId,
        private ResolverInterface $dns,
    ) {
        $this->parser = new Parser(
            fn(\SimpleXMLElement $node) => (new \Moxl\Xec\Handler($this->user))->handle($node)
        );
    }

    public function attachWebsocket(WebSocket $websocket)
    {
        $this->websocket = $websocket;
    }

    public function attachUser(User $user)
    {
        $this->user = $user;
    }

    public function register(string $host)
    {
        $this->host = $host;
        $results = [];

        timeout($this->dns->resolveAll('_xmpps-client._tcp.' . $host, Message::TYPE_SRV), 3.0)
            ->then(
                function ($resolved) use (&$results) {
                    $results['directtls'] = $resolved;
                    $this->handleClientDNS($results);
                },
                function ($rejected) use (&$results) {
                    $results['directtls'] = false;
                    $this->handleClientDNS($results);
                }
            );

        timeout($this->dns->resolveAll('_xmpp-client._tcp.' . $host, Message::TYPE_SRV), 3.0)
            ->then(
                function ($resolved) use (&$results) {
                    $results['starttls'] = $resolved;
                    $this->handleClientDNS($results);
                },
                function ($rejected) use (&$results) {
                    $results['starttls'] = false;
                    $this->handleClientDNS($results);
                }
            );
    }

    public function connected(): bool
    {
        return $this->connection != null;
    }

    public function logout(): void
    {
        \Moxl\Stanza\Stream::end();

        if ($this->connected()) {
            $this->connection->close();
        }
    }

    public function handleJSON($request)
    {
        (new RPC($this->user))->handleJSON($request);
    }

    public function writeXMPP($xml)
    {
        if ($this->connection) {
            $this->connection->write(trim($xml));
        }

        if (config('daemon.debug')) {
            logOut(colorize(trim($xml) . ' ', 'yellow'), '>>> XMPP sent');
        }
    }

    private function xmppBehaviour(Connection $connection)
    {
        $this->connection = $connection;
        Wrapper::getInstance()->iterate('socket_connected');

        if (config('daemon.verbose')) {
            logOut(colorize('XMPP socket launched', 'blue'));
            logOut(" launched : " . \humanSize(memory_get_usage()));
        }

        $this->connection->on('data', function ($message) {
            if (!empty($message)) {

                if (config('daemon.debug')) {
                    logOut(colorize($message . ' ', 'yellow'), '<<< XMPP received');
                }

                if ($message == '</stream:stream>') {
                    $this->connection->close();
                    shutdown();
                } elseif (
                    $message == "<proceed xmlns='urn:ietf:params:xml:ns:xmpp-tls'/>"
                    || $message == '<proceed xmlns="urn:ietf:params:xml:ns:xmpp-tls"/>'
                ) {
                    $this->enableEncryption($this->connection)->then(
                        function () {
                            $session = Session::instance();
                            \Moxl\Stanza\Stream::init($this->host, $session->get('jid'));
                        }
                    );
                }

                global $timestampReceive;
                $timestampReceive = time();

                if (!$this->parser->parse($message)) {
                    logOut($this->parser->getError());
                }
            }
        });

        $this->connection->on('error', fn() => shutdown());
        $this->connection->on('close', fn() => shutdown());

        // And we say that we are ready !
        $obj = new \StdClass;
        $obj->func = 'registered';

        fwrite(STDERR, 'registered');
        $this->websocket->send(json_encode($obj));
    }

    private function handleClientDNS(array $results)
    {
        if (count($results) > 1) {
            $port = 5222;
            $directTLSSocket = false;
            $host = null;

            if (
                $results['directtls'] !== false && $results['directtls'][0]['target'] !== '.'
                && $results['starttls'] !== false && $results['starttls'][0]['target'] !== '.'
            ) {
                if ($results['starttls'][0]['priority'] < $results['directtls'][0]['priority']) {
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
                $host = $this->host;
            }

            $socket = $directTLSSocket ? 'tls://' : 'tcp://';
            $socket .= $host . ':' . $port;

            logOut(colorize('Connect to ' . $socket . ', peer_name: ' . $host, 'blue'));

            $this->connector = new HappyEyeBallsConnector(
                null,
                new Connector([
                    'timeout' => 5.0,
                    'tls' => [
                        'SNI_enabled' => true,
                        'allow_self_signed' => false,
                        'peer_name' => $host
                    ]
                ]),
                $this->dns
            );

            $this->connector->connect($socket)->then(
                fn($connection) => $this->xmppBehaviour($connection),
                function (\Exception $error) {
                    logOut(colorize($error->getMessage(), 'red'));
                    Wrapper::getInstance()->iterate('timeout_error');
                }
            );
        }
    }

    private function enableEncryption($connection)
    {
        global $loop;

        $encryption = new \React\Socket\StreamEncryption($loop, false);
        logOut(colorize('Enable TLS on the socket', 'blue'));

        stream_context_set_option($connection->stream, 'ssl', 'SNI_enabled', true);
        stream_context_set_option($connection->stream, 'ssl', 'peer_name', $this->host);
        stream_context_set_option($connection->stream, 'ssl', 'allow_self_signed', false);

        return $encryption->enable($connection)->then(
            fn() => logOut(colorize('TLS enabled', 'blue')),
            function ($error) {
                logOut(colorize('TLS error ' . $error->getMessage(), 'blue'));
                Wrapper::getInstance()->iterate('ssl_error');
                shutdown();
            }
        );
    }
}
