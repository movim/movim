<?php

namespace App\Workers\Galener;

use App\User;
use Illuminate\Support\Collection;
use Movim\Jid;
use Movim\Librairies\JingletoSDP;
use Movim\Librairies\SDPtoJingle;
use Moxl\Stanza\Jingle;
use Moxl\Stanza\Ping;
use Ratchet\Client\WebSocket;

class Connection
{
    private WebSocket $websocket;
    private string $id;
    private ?string $jingleSid = null;
    private ?string $streamId = null;

    // Specific Jingle sid to send the screenshare stream
    private ?string $screenshareSid = null;
    private ?string $screenshareStreamId = null;

    private array $websocketBuffer = [];

    private Collection $users;
    private Collection $contents;
    private Collection $acceptedContents;

    private const GALENE_LABEL_TO_CONTENT_CATEGORY = [
        'camera' => 'speaker',
        'screenshare' => 'slides',
        'video' => 'speaker'
    ];

    public function __construct(
        private Conference &$conference,
        public Jid $jid,
        private GaleneAPIClient $apiClient,
    ) {
        $this->id = generateUUID();
        $this->streamId = generateUUID();

        $this->users = collect();
        $this->contents = collect();
        $this->acceptedContents = collect();

        $this->send([
            'type' => 'join',
            'kind' => 'join',
            'group' => $this->conference->jid,
            'username' => $this->jid->bareJid(),
            'password' => GaleneAPIClient::USER_WILDCARD_PASSWORD,
        ]);

        $this->send([
            'type' => 'request',
            'request' => [
                '' => ['audio', 'video']
            ]
        ]);

        $this->apiClient->addUserToGroup($this->conference->jid, $this->jid->bareJid())->then(function () {
            \Ratchet\Client\connect('ws://localhost:' . $this->apiClient->port . '/ws', headers: [
                'Origin' => 'https://localhost:' . $this->apiClient->port
            ])->then(function ($websocket) {
                $this->websocket = $websocket;
                $this->websocket->on('message', function ($message) {
                    $json = json_decode($message);
                    switch ($json->type) {
                        case 'add':
                            $this->users->put((string)$json->id, new Jid($json->username));
                            break;
                        case 'handshake':
                            $this->send(['type' => 'ping']);
                            break;
                        case 'ping':
                            $this->pingCounter++;
                            $this->conference->sendXMPP($this->iq(
                                type: 'set',
                                from: $this->conference->getSFUJid(),
                                id: generateUUID(),
                                xml: Ping::entity()
                            ));
                            break;
                        case 'offer':
                            $this->users->put((string)$json->id, new Jid($json->username));

                            $stj = new SDPtoJingle(
                                user: new User([
                                    'id' => $this->conference->getSFUJid()
                                ]),
                                sdp: $json->sdp,
                                sid: $this->jingleSid,
                                responder: (string)$this->jid,
                                action: 'content-add',
                                jingleParticipant: (string)$this->users->get((string)$json->id),
                                contentCategory: self::GALENE_LABEL_TO_CONTENT_CATEGORY[(string)$json->label]
                            );

                            $this->contents->put((string)$json->id, [
                                'sdp' => (string)$json->sdp,
                                'label' => (string)$json->label
                            ]);
                            $this->acceptedContents->put((string)$json->id, false);
                            $this->conference->sendXMPP($this->iq(
                                type: 'set',
                                from: $this->conference->getSFUJid(),
                                id: generateUUID(),
                                xml: $stj->generate()
                            ));
                            break;
                        case 'answer':
                            $isScreenshare = $this->screenshareStreamId !== null
                                && (string)$json->id === $this->screenshareStreamId;

                            $stj = new SDPtoJingle(
                                user: new User(['id' => $this->conference->getSFUJid()]),
                                sdp: $json->sdp,
                                sid: $isScreenshare ? $this->screenshareSid : $this->jingleSid,
                                responder: (string)$this->jid,
                                action: 'session-accept'
                            );

                            $this->conference->sendXMPP($this->iq(
                                type: 'set',
                                from: $this->conference->getSFUJid(),
                                id: generateUUID(),
                                xml: $stj->generate()
                            ));
                            break;
                        case 'close':
                            foreach ($this->contents as $key => $content) {
                                if (
                                    (string)$key == (string)$json->id
                                    && ($this->users->has((string)$json->id))
                                ) {
                                    $stj = new SDPtoJingle(
                                        user: new User([
                                            'id' => $this->conference->getSFUJid()
                                        ]),
                                        sdp: $content['sdp'],
                                        sid: $this->jingleSid,
                                        responder: (string)$this->jid,
                                        action: 'content-remove',
                                        jingleParticipant: (string)$this->users->get((string)$json->id),
                                        contentCategory: self::GALENE_LABEL_TO_CONTENT_CATEGORY[$content['label']]
                                    );

                                    $this->conference->sendXMPP($this->iq(
                                        type: 'set',
                                        from: $this->conference->getSFUJid(),
                                        id: generateUUID(),
                                        xml: $stj->generate()
                                    ));

                                    $this->contents->forget($key);
                                    $this->acceptedContents->forget($key);
                                    $this->users->forget($key);
                                    break;
                                }
                            };
                            break;
                        case 'ice':
                            $stj = new SDPtoJingle(
                                user: new User([
                                    'id' => $this->conference->getSFUJid()
                                ]),
                                sdp: 'a=' . $json->candidate->candidate,
                                sid: $this->jingleSid,
                                responder: (string)$this->jid,
                                action: 'transport-info',
                                mid: $json->candidate->sdpMid,
                                ufrag: $json->candidate->usernameFragment,
                                jingleParticipant: (string)$this->users->get((string)$json->id),
                            );

                            if ($this->users->has($json->id)) {
                                $this->conference->sendXMPP($this->iq(
                                    type: 'set',
                                    from: $this->conference->getSFUJid(),
                                    id: generateUUID(),
                                    xml: $stj->generate()
                                ));
                            }


                            break;
                    }
                });

                $this->websocket->send(json_encode([
                    'type' => 'handshake',
                    'version' => ['2'],
                    'id' => $this->id
                ]));
            }, function ($e) {
                \logError('❌ Galener: ' . $e->getMessage());
            });
        }, function ($e) {
            \logError('❌ Galener: ' . $e->getMessage());
        });
    }

    public function end()
    {
        $this->websocket->close();
        //$this->apiClient->removeUserFromGroup($this->conference->id, $this->jid->bareJid());
    }

    public function xmppOfferOrScreenshare(XMPPNode $node)
    {
        if ($this->jingleSid === null) {
            $this->xmppOffer($node);
        } else {
            $this->xmppScreenshareOffer($node);
        }
    }

    public function xmppScreenshareOffer(XMPPNode $node)
    {
        $this->screenshareSid = (string)$node->stanza->jingle->attributes()->sid;
        $this->screenshareStreamId = generateUUID();

        $this->send([
            'type' => 'offer',
            'source' => $this->id,
            'username' => $this->jid->bareJid(),
            'kind' => '',
            'id' => $this->screenshareStreamId,
            'replace' => null,
            'label' => 'screenshare',
            'sdp' => (new JingletoSDP($node->stanza->jingle))->generate() . "\r\n"
        ]);
    }

    public function xmppScreenshareTerminate(XMPPNode $node)
    {
        $sid = (string)$node->stanza->jingle->attributes()->sid;

        if ($sid === $this->screenshareSid && $this->screenshareStreamId) {
            $this->send(['type' => 'close', 'id' => $this->screenshareStreamId]);
            $this->screenshareSid = null;
            $this->screenshareStreamId = null;
        }
    }

    public function xmppPong(XMPPNode $node)
    {
        $this->send(['type' => 'pong']);
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

    public function xmppContentAccept(XMPPNode $node)
    {
        if ($node->stanza->jingle->{'jingle-participant'}?->attributes()->xmlns == SDPtoJingle::JINGLE_PARTICIPANT_XMLNS) {
            $participantJid = (string)$node->stanza->jingle->{'jingle-participant'}->attributes()->participant;
            $id = null;

            foreach ($this->users as $galeneId => $jid) {
                if ((string)$jid == $participantJid) {
                    if ($this->acceptedContents->get($galeneId, false) == false) {
                        $id = $galeneId;
                        $this->acceptedContents->put($galeneId, true);
                        break;
                    }
                }
            };

            if ($id) {
                $this->send([
                    'type' => 'answer',
                    'id' => $id,
                    'sdp' => (new JingletoSDP($node->stanza->jingle))->generate() . "\r\n"
                ]);
            }
        }
    }

    public function xmppCandidate(XMPPNode $node)
    {
        $sid = (string)$node->stanza->jingle->attributes()->sid;
        $streamId = ($this->screenshareSid !== null && $sid === $this->screenshareSid)
            ? $this->screenshareStreamId
            : $this->streamId;

        $jts = (new JingletoSDP($node->stanza->jingle));
        $candidate = $jts->generate();

        preg_match('/(candidate.*)/', $candidate, $outputCandidates);

        $this->send([
            'type' => 'ice',
            'username' => $this->jid->bareJid(),
            'id' => $streamId,
            'candidate' => [
                'candidate' => $outputCandidates[0],
                'sdpMLineIndex' => (int)$jts->name,
                'sdpMid' => (string)$jts->name,
            ],
        ]);
    }

    public function xmppMute(XMPPNode $node)
    {
        $jingle = Jingle::sessionMute(
            sid: $this->jingleSid,
            name: 'mid' . (string)$node->stanza->jingle->mute->attributes()->name
        );

        $jingleParticipant = $jingle->ownerDocument->createElement('jingle-participant');
        $jingleParticipant->setAttribute('xmlns', SDPtoJingle::JINGLE_PARTICIPANT_XMLNS);
        $jingleParticipant->setAttribute('participant', $this->jid->bareJid());
        $jingle->appendChild($jingleParticipant);

        foreach ($this->conference->connections as $connection) {
            if ($connection->jid != $this->jid) {
                $this->conference->sendXMPP($this->iq(
                    type: 'set',
                    from: $this->conference->getSFUJid(),
                    id: generateUUID(),
                    to: $connection->jid,
                    xml: $jingle
                ));
            }
        }
    }

    public function xmppUnmute(XMPPNode $node)
    {
        $jingle = Jingle::sessionUnmute(
            sid: $this->jingleSid,
            name: 'mid' . (string)$node->stanza->jingle->unmute->attributes()->name
        );

        $jingleParticipant = $jingle->ownerDocument->createElement('jingle-participant');
        $jingleParticipant->setAttribute('xmlns', SDPtoJingle::JINGLE_PARTICIPANT_XMLNS);
        $jingleParticipant->setAttribute('participant', $this->jid->bareJid());
        $jingle->appendChild($jingleParticipant);

        foreach ($this->conference->connections as $connection) {
            if ($connection->jid != $this->jid) {
                $this->conference->sendXMPP($this->iq(
                    type: 'set',
                    from: $this->conference->getSFUJid(),
                    id: generateUUID(),
                    to: $connection->jid,
                    xml: $jingle
                ));
            }
        }
    }

    private function send(array $array)
    {
        if (!isset($this->websocket)) {
            array_push($this->websocketBuffer, $array);
            return;
        }

        if (!empty($this->websocketBuffer)) {
            foreach ($this->websocketBuffer as $bufferedArray) {
                $this->websocket->send(json_encode($bufferedArray));
            }

            $this->websocketBuffer = [];
        }

        $this->websocket->send(json_encode($array));
    }

    private function iq(
        string $type,
        string $from,
        string $id,
        ?\DOMNode $xml = null,
        ?string $to = null
    ): \DOMDocument {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $iq = $dom->createElementNS('jabber:client', 'iq');
        $dom->appendChild($iq);
        $iq->setAttribute('from', $from);
        $iq->setAttribute('to', $to != null ? $to :  $this->jid);
        $iq->setAttribute('type', $type);
        $iq->setAttribute('id', $id);

        if ($xml != false) {
            $xml = $dom->importNode($xml, true);
            $iq->appendChild($xml);
        }

        return $dom;
    }
}
