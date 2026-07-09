<?php

namespace App\Workers\Galener;

use Movim\Jid;

class Conference
{
    private array $connections;

    public function __construct(
        public string $id,
        private $sendXMPP,
        private GaleneAPIClient $apiClient,
    ) {
        $this->apiClient->createGroup($id);
    }

    public function sendXMPP(?\DOMDocument $dom = null)
    {
        ($this->sendXMPP)($dom);
    }

    public function getJid(): string
    {
        return $this->id . '@sfu.movim.eu';
    }

    public function addConnection(Jid $jid)
    {
        $this->connections[(string)$jid] = new Connection(conference: $this, jid: $jid, apiClient: $this->apiClient);
    }

    public function getConnection(Jid $jid): ?Connection
    {
        if (array_key_exists((string)$jid, $this->connections)) {
            return $this->connections[(string)$jid];
        }

        return null;
    }
}
