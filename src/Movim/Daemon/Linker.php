<?php

namespace Movim\Daemon;

use App\User;

use Movim\Daemon\Linker\ChatOwnState;
use Movim\Daemon\Linker\ChatroomPings;
use Movim\Daemon\Linker\ChatStates;
use Movim\Daemon\Linker\CurrentCall;
use Movim\Daemon\Linker\Locale;
use Movim\Daemon\Linker\PresenceBuffer;
use Movim\Daemon\Linker\Session;
use Movim\RPC;
use Movim\Widget\Wrapper;
use Moxl\Authentication;
use Moxl\Parser;
use Moxl\Xec\Payload\Packet;
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
    public ?User $user = null;

    public ?PresenceBuffer $presenceBuffer = null;
    public ?ChatOwnState $chatOwnState = null;
    public ?CurrentCall $currentCall = null;
    public ?ChatroomPings $chatroomPings = null;
    public ?ChatStates $chatStates = null;
    public ?Locale $locale = null;
    public Authentication $authentication;
    public Session $session;
    public ?string $timezone = 'UTC';

    private ?string $timestampSend = null;
    private ?string $timestampReceive = null;

    public function __construct(
        private LinkersManager $linkersManager,
        private ResolverInterface $dns,
        private string $sessionId,
        private string $browserLocale
    ) {
        $this->parser = new Parser(
            fn(\SimpleXMLElement $node) => (new \Moxl\Xec\Handler(
                user: $this->user,
                sessionId: $sessionId
            ))->handle($node)
        );

        $this->session = new Session;
        $this->authentication = new Authentication;
        $this->locale = new Locale($browserLocale);
        $this->locale->loadTranslations();

        // Temporary linker killer
        global $loop;

        $loop->addPeriodicTimer(5, function () {
            if (($this->timestampSend < time() - 3600 * 12 /* 24h */ || $this->timestampReceive < time() - 60 * 30 /* 30min */)
                && $this->connected()
            ) {
                $this->logout();
            }
        });
    }

    public function attachUser(User $user)
    {
        $this->user = $user;

        // Presence buffer
        $this->presenceBuffer = new PresenceBuffer($this->user);
        global $loop;
        $loop->addPeriodicTimer(1, fn() => $this->presenceBuffer->save());

        $this->chatOwnState = new ChatOwnState($this->user);
        $this->currentCall = new CurrentCall($this->user, $this->sessionId);
        $this->chatroomPings = new ChatroomPings($this->user);
        $this->chatStates = new ChatStates($this->user);
        $this->locale->setUser($this->user);
        $this->locale->loadTranslations();
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
        $this->writeXMPP(\Moxl\Stanza\Stream::end());

        if ($this->connected()) {
            $this->connection->close();
        }
    }

    public function handleJSON(\stdClass $request, ?string $sessionId = null)
    {
        (new RPC($this->user))->handleJSON($request, $sessionId);
    }

    public function writeXMPP($xml)
    {
        if ($this->connection && !empty($xml)) {
            $this->timestampSend = time();
            $this->connection->write(trim($xml));

            if (config('daemon.debug')) {
                logOut(colorize(trim($xml) . ' ', 'yellow'), type: '>>> XMPP sent', sid: $this->sessionId);
            }
        }
    }

    function writeOut(\stdClass $message)
    {
        $this->linkersManager->sendWebsocket($this->sessionId, $message);
    }

    private function xmppBehaviour(Connection $connection)
    {
        $this->connection = $connection;
        Wrapper::getInstance()->iterate('socket_connected', user: $this->user, sessionId: $this->sessionId);

        if (config('daemon.verbose')) {
            logOut(colorize('XMPP socket launched', 'green'), sid: $this->sessionId);
        }

        $this->connection->on('data', function ($message) {
            if (!empty($message)) {

                if (config('daemon.debug')) {
                    logOut(colorize($message . ' ', 'yellow'), type: '<<< XMPP received', sid: $this->sessionId);
                }

                if ($message == '</stream:stream>') {
                    $this->connection->close();
                } elseif (
                    $message == "<proceed xmlns='urn:ietf:params:xml:ns:xmpp-tls'/>"
                    || $message == '<proceed xmlns="urn:ietf:params:xml:ns:xmpp-tls"/>'
                ) {
                    $this->enableEncryption($this->connection)->then(
                        function () {
                            $this->writeXMPP(\Moxl\Stanza\Stream::init($this->host, $this->user?->id));
                        }
                    );
                }

                $this->timestampReceive = time();

                if (!$this->parser->parse($message)) {
                    logOut($this->parser->getError());
                }
            }
        });

        $this->connection->on('error', fn() => $this->linkersManager->closeLinker($this->sessionId));
        $this->connection->on('close', fn() => $this->linkersManager->closeLinker($this->sessionId));

        // And we say that we are ready!
        $message = new \stdClass;
        $message->registered = true;
        $this->writeOut($message);

        $message = new \stdClass;
        $message->func = 'registered';
        $this->writeOut($message);
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
                    logOut(colorize('Picked STARTTLS', 'blue'), sid: $this->sessionId);
                } else {
                    $host = $results['directtls'][0]['target'];
                    $port = $results['directtls'][0]['port'];
                    $directTLSSocket = true;
                    logOut(colorize('Picked DirectTLS', 'blue'), sid: $this->sessionId);
                }
            } elseif ($results['directtls'] !== false && $results['directtls'][0]['target'] !== '.') {
                $host = $results['directtls'][0]['target'];
                $port = $results['directtls'][0]['port'];
                $directTLSSocket = true;
                logOut(colorize('Picked DirectTLS', 'blue'), sid: $this->sessionId);
            } elseif ($results['starttls'] !== false && $results['starttls'][0]['target'] !== '.') {
                $host = $results['starttls'][0]['target'];
                $port = $results['starttls'][0]['port'];
                logOut(colorize('Picked STARTTLS', 'blue'), sid: $this->sessionId);
            } else {
                // No SRV, we fallback to the default host
                $host = $this->host;
            }

            $socket = $directTLSSocket ? 'tls://' : 'tcp://';
            $socket .= $host . ':' . $port;

            logOut(colorize('Connect to ' . $socket . ', peer_name: ' . $host, 'blue'), sid: $this->sessionId);

            $this->connector = new HappyEyeBallsConnector(
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

            $this->connector->connect($socket)->then(
                fn($connection) => $this->xmppBehaviour($connection),
                function (\Exception $error) {
                    logOut(colorize($error->getMessage(), 'red'), sid: $this->sessionId);
                    Wrapper::getInstance()->iterate('connection_error', (new Packet)->pack($error->getMessage()), sessionId: $this->sessionId);
                }
            );
        }
    }

    private function enableEncryption($connection)
    {
        global $loop;

        $encryption = new \React\Socket\StreamEncryption($loop, false);
        logOut(colorize('Enable TLS on the socket', 'blue'), sid: $this->sessionId);

        stream_context_set_option($connection->stream, 'ssl', 'SNI_enabled', true);
        stream_context_set_option($connection->stream, 'ssl', 'peer_name', $this->host);
        stream_context_set_option($connection->stream, 'ssl', 'allow_self_signed', false);

        return $encryption->enable($connection)->then(
            fn() => logOut(colorize('TLS enabled', 'blue'), sid: $this->sessionId),
            function ($error) {
                logOut(colorize('TLS error ' . $error->getMessage(), 'blue'), sid: $this->sessionId);
                Wrapper::getInstance()->iterate('ssl_error', sessionId: $this->sessionId); // TODO give context
                $this->linkerManager->closeLinker($this->sessionId);
            }
        );
    }
}
