<?php

namespace App\Workers\Galener;

use App\User;
use Movim\Jid;
use Movim\Librairies\JingletoSDP;
use Movim\Librairies\SDPtoJingle;
use Ratchet\Client\WebSocket;

class Connection
{
    private WebSocket $websocket;
    private string $id;
    private string $streamId;
    private string $jingleSid;

    private array $websocketBuffer = [];

    public function __construct(
        private Conference &$conference,
        private Jid $jid,
        private GaleneAPIClient $apiClient,
    ) {
        $this->id = generateUUID();
        $this->streamId = generateUUID();

        $this->send([
            'type' => 'join',
            'kind' => 'join',
            'group' => $this->conference->id,
            'username' => $this->jid->bareJid(),
            'password' => GaleneAPIClient::USER_WILDCARD_PASSWORD,
        ]);

        $this->send([
            'type' => 'request',
            'request' => [
                '' => ['audio', 'video']
            ]
        ]);

        $this->apiClient->addUserToGroup($this->conference->id, $this->jid->bareJid())->then(function () {
            \Ratchet\Client\connect('ws://localhost:' . $this->apiClient->port . '/ws', headers: [
                'Origin' => 'https://localhost:' . $this->apiClient->port
            ])->then(function ($websocket) {
                $this->websocket = $websocket;
                $this->websocket->on('message', function ($message) {
                    $json = json_decode($message);
                    \logDebug('⭐' . $message);
                    switch ($json->type) {
                        case 'handshake':
                            $this->send(['type' => 'ping']);
                            break;
                        case 'ping':
                            $this->send(['type' => 'pong']);
                            break;
                        case 'answer':
                            $stj = new SDPtoJingle(
                                user: new User([
                                    'id' => $this->conference->getJid()
                                ]),
                                sdp: $json->sdp,
                                sid: $this->jingleSid,
                                responder: (string)$this->jid,
                                action: 'session-accept'
                            );

                            $this->conference->sendXMPP($this->iq(
                                type: 'set',
                                from: $this->conference->getJid(),
                                id: generateUUID(),
                                xml: $stj->generate()
                            ));
                            break;
                        case 'ice':
                            $stj = new SDPtoJingle(
                                user: new User([
                                    'id' => $this->conference->getJid()
                                ]),
                                sdp: 'a=' . $json->candidate->candidate,
                                sid: $this->jingleSid,
                                responder: (string)$this->jid,
                                action: 'transport-info',
                                mid: $json->candidate->sdpMid,
                                ufrag: $json->candidate->usernameFragment
                            );

                            $this->conference->sendXMPP($this->iq(
                                type: 'set',
                                from: $this->conference->getJid(),
                                id: generateUUID(),
                                xml: $stj->generate()
                            ));

                            break;
                    }
                });

                $this->websocket->send(json_encode([
                    'type' => 'handshake',
                    'version' => ['2'],
                    'id' => $this->id
                ]));
            }, function ($e) {
                \logDebug('❌ ' . $e->getMessage());
            });
        }, function ($e) {
            \logDebug('🧐❌ ' . $e->getMessage());
        });
    }

    public function xmppOffer(XMPPNode $node)
    {
        $this->jingleSid = $node->stanza->jingle->attributes()->sid;

        $this->send([
            'type' => 'offer',
            'source' => $this->id,
            'username' => $this->jid->bareJid(),
            'kind' => '',
            'id' => $this->streamId,
            'replace' => null,
            'label' => 'camera',
            'sdp' => (new JingletoSDP($node->stanza->jingle))->generate() . "\r\n"
        ]);
    }

    public function xmppCandidate(XMPPNode $node)
    {
        $jts = (new JingletoSDP($node->stanza->jingle));
        $candidate = $jts->generate();

        preg_match('/(candidate.*)/', $candidate, $outputCandidates);
        preg_match('/ufrag:(.{8})/', $candidate, $ufragCandidates);

        $this->send([
            'type' => 'ice',
            'username' => $this->jid->bareJid(),
            'id' => $this->streamId,
            'candidate' => [
                'candidate' => $outputCandidates[0],
                //'usernameFragment' => $ufragCandidates[1],
                'sdpMLineIndex' => (int)$jts->name,
                'sdpMid' => (string)$jts->name,
            ],
        ]);
    }

    private function send(array $array)
    {
        if (!isset($this->websocket)) {
            array_push($this->websocketBuffer, $array);
            return;
        }

        if (!empty($this->websocketBuffer)) {
            foreach ($this->websocketBuffer as $bufferedArray) {
                \logDebug('✌️ ' . json_encode($bufferedArray));
                $this->websocket->send(json_encode($bufferedArray));
            }

            $this->websocketBuffer = [];
        }

        \logDebug('✌️ ' . json_encode($array));
        $this->websocket->send(json_encode($array));
    }

    private function iq(string $type, string $from, string $id, ?\DOMNode $xml = null): \DOMDocument
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $iq = $dom->createElementNS('jabber:client', 'iq');
        $dom->appendChild($iq);
        $iq->setAttribute('from', $from);
        $iq->setAttribute('type', $type);
        $iq->setAttribute('id', $id);
        $iq->setAttribute('to', $this->jid);

        if ($xml != false) {
            $xml = $dom->importNode($xml, true);
            $iq->appendChild($xml);
        }

        return $dom;
    }
}
