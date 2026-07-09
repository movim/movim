<?php

namespace App\Workers\Galener;

use DOMDocument;
use Movim\Jid;
use Moxl\Stanza\Presence;

class Conference
{
    private array $connections;

    public function __construct(
        public string $jid,
        private $sendXMPP,
        private GaleneAPIClient $apiClient,
    ) {
        $this->apiClient->createGroup($jid);
    }

    public function sendXMPP(?\DOMDocument $dom = null)
    {
        ($this->sendXMPP)($dom);
    }

    public function join()
    {
        $this->sendXMPP($this->generatePresence());
    }

    public function getSFUJid(): string
    {
        return generateUUID($this->jid) . '@' . config('galener.xmpp_host');
    }

    public function addConnection(Jid $jid)
    {
        $this->connections[(string)$jid] = new Connection(conference: $this, jid: $jid, apiClient: $this->apiClient);
    }

    public function removeConnection(Jid $jid)
    {
        if (array_key_exists((string)$jid, $this->connections)) {
            $this->connections[(string)$jid]->end();
            unset($this->connections[(string)$jid]);
        }
    }

    public function getConnection(Jid $jid): ?Connection
    {
        if (array_key_exists((string)$jid, $this->connections)) {
            return $this->connections[(string)$jid];
        }

        return null;
    }

    private function generatePresence(): DOMDocument
    {
        $presence = Presence::maker(
            to: (string)$this->jid . '/' . config('galener.xmpp_host'),
            from: $this->getSFUJid(),
            muc: true
        );

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

        return $presence;
    }
}
