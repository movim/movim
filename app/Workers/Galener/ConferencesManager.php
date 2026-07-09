<?php

namespace App\Workers\Galener;

use Movim\Jid;

class ConferencesManager
{
    public function __construct(
        private GaleneAPIClient $apiClient,
        private $sendXMPP,
        private array $conferences = []
    ) {}

    public function sendXMPP(?\DOMDocument $dom = null)
    {
        ($this->sendXMPP)($dom);
    }

    public function createOrGetConference(Jid $jid): Conference
    {
        if ($conference = $this->getConference($jid)) {
            return $conference;
        }

        $this->conferences[(string)$jid] = new Conference(
            jid: $jid,
            apiClient: $this->apiClient,
            sendXMPP: $this->sendXMPP
        );

        return $this->conferences[(string)$jid];
    }

    public function getConference(Jid $jid): ?Conference
    {
        if (array_key_exists((string)$jid, $this->conferences)) {
            return $this->conferences[(string)$jid];
        }

        return null;
    }
}
