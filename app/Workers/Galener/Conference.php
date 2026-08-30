<?php

namespace App\Workers\Galener;

use Carbon\Carbon;
use DOMDocument;
use Movim\Jid;
use Moxl\Stanza\Message;
use Moxl\Stanza\Muc;
use Moxl\Stanza\Presence;
use Moxl\Stanza\Register;

class Conference
{
    public array $connections = [];
    private array $members = [];
    private bool $connected = false;
    private string $resource;
    private ?Carbon $startedAt = null;

    public const CONFERENCE_STARTED_AT_XMLNS = '{https://movim.eu}conference_started_at';

    public function __construct(
        public string $jid,
        private $sendXMPP,
        private GaleneAPIClient $apiClient,
    ) {
        $this->apiClient->createGroup($jid);
        $this->resource = config('galener.xmpp_host') . '_' . generateKey(6);
    }

    /**
     * Connections
     */

    public function addConnection(Jid $jid)
    {
        if ($this->connected && array_key_exists($jid->bareJid(), $this->members)) {
            $this->connections[(string)$jid] = new Connection(conference: $this, jid: $jid, apiClient: $this->apiClient);

            if (count($this->connections) == 1) {
                $this->startedAt = Carbon::now();
            }

            $this->sendXMPP($this->generatePresence());
        }
    }

    public function removeConnection(Jid $jid)
    {
        if (array_key_exists((string)$jid, $this->connections)) {
            $this->connections[(string)$jid]->end();
            unset($this->connections[(string)$jid]);

            if (count($this->connections) == 0) {
                $this->startedAt = null;
            }

            $this->sendXMPP($this->generatePresence());
        }
    }

    public function getConnection(Jid $jid): ?Connection
    {
        if (array_key_exists((string)$jid, $this->connections)) {
            return $this->connections[(string)$jid];
        }

        return null;
    }

    /**
     * XMPP actions
     */

    public function sendXMPP(?\DOMDocument $dom = null)
    {
        ($this->sendXMPP)($dom);
    }

    public function xmppJoin()
    {
        $this->sendXMPP(Presence::maker(
            to: (string)$this->jid . '/sfu',
            from: config('galener.xmpp_host'),
            muc: true
        ));

        // ...and we join
        $this->sendXMPP($this->generatePresence());
    }

    public function xmppSetAdmin()
    {
        // We set the conference user admin in the room
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $iq = $dom->createElementNS('jabber:client', 'iq');
        $dom->appendChild($iq);
        $iq->setAttribute('to', (string)$this->jid);
        $iq->setAttribute('from', config('galener.xmpp_host'));
        $iq->setAttribute('type', 'set');
        $iq->setAttribute('id', \generateKey());

        $xml = $dom->importNode(Muc::changeAffiliation($this->getSFUJid(), 'admin'), true);
        $iq->appendChild($xml);

        $this->sendXMPP($dom);
    }

    public function xmppLeaveAndDestroy()
    {
        $this->sendXMPP(Presence::maker(
            to: $this->getRoomJid(),
            from: $this->getSFUJid(),
            muc: true,
            type: 'unavailable'
        ));

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $iq = $dom->createElementNS('jabber:client', 'iq');
        $dom->appendChild($iq);
        $iq->setAttribute('to', (string)$this->jid);
        $iq->setAttribute('from', $this->getSFUJid());
        $iq->setAttribute('type', 'set');
        $iq->setAttribute('id', \generateKey());

        $xml = $dom->importNode(Register::remove(), true);
        $iq->appendChild($xml);

        $this->sendXMPP($dom);

        $this->apiClient->deleteGroup($this->jid);
    }

    public function xmppNotAdminMessage()
    {
        $xml = Message::maker(
            to: (string)$this->jid,
            messageId: \generateKey(),
            from: $this->getSFUJid(),
            type: 'groupchat',
            content: 'The SFU must be an owner of the room to join',
        );

        $this->sendXMPP($xml);
    }

    public function xmppAddMember(Jid $jid)
    {
        $this->members[$jid->bareJid()] = $jid;

        // The Conference is connected, we republish the complete presence
        if ($jid->bareJid() == $this->getSFUJid() && $this->connected == false) {
            $this->connected = true;
            $this->sendXMPP($this->generatePresence());
        }
    }

    public function getSFUJid(): string
    {
        return hash('sha1', $this->jid) . '@' . config('galener.xmpp_host');
    }

    public function getRoomJid(): string
    {
        return (string)$this->jid . '/' . $this->resource;
    }

    private function generatePresence(): DOMDocument
    {
        $presence = Presence::maker(
            to: $this->getRoomJid(),
            from: $this->getSFUJid(),
            muc: true,
            withCaps: false
        );

        // Adding capabilities

        $c = $presence->createElementNS('urn:xmpp:caps', 'c');
        $hash = $presence->createElement('hash', \Moxl\Utils::getOwnGalenerCapabilityHash());
        $hash->setAttribute('xmlns', 'urn:xmpp:hashes:2');
        $hash->setAttribute('algo', \Moxl\Utils::CAPABILITY_HASH_ALGORITHM);
        $c->appendChild($hash);

        $presence->documentElement->appendChild($c);

        /*$c = $presence->createElementNS('http://jabber.org/protocol/caps', 'c');
        $c->setAttribute('hash', 'sha-1');
        $c->setAttribute('node', 'https://movim.eu/');
        $c->setAttribute('ver', \Moxl\Utils::generateCaps(galener: true));
        $presence->documentElement->appendChild($c);*/

        // Adding COIN

        if ($this->connected) {
            $conferenceInfo = $presence->createElement('conference-info');
            $conferenceInfo->setAttribute('xmlns', 'urn:ietf:params:xml:ns:conference-info');
            $conferenceInfo->setAttribute('entity', 'xmpp:' . (string)$this->members[$this->getSFUJid()]);
            $conferenceInfo->setAttribute('state', 'full');
            $conferenceInfo->setAttribute('version', '1');

            if ($this->startedAt) {
                $conferenceStartedAt = $presence->createElement('conference-started-at');
                $conferenceStartedAt->setAttribute('xmlns', self::CONFERENCE_STARTED_AT_XMLNS);
                $conferenceStartedAt->setAttribute('started-at', $this->startedAt->toISOString());
                $conferenceInfo->appendChild($conferenceStartedAt);
            }

            $conferenceState = $presence->createElement('conference-state');
            $conferenceState->appendChild($presence->createElement('user-count', count($this->connections)));
            $conferenceInfo->appendChild($conferenceState);

            $users = $presence->createElement('users');
            $conferenceInfo->appendChild($users);

            foreach ($this->connections as $connection) {
                $user = $presence->createElement('user');
                $user->setAttribute('entity', 'xmpp:' . $connection->jid->bareJid());
                $user->setAttribute('state', 'full');
                $users->appendChild($user);
            }

            $presence->documentElement->appendChild($conferenceInfo);
        }

        return $presence;
    }
}
