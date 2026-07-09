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

        $this->conferences[$jid->bareJid()] = new Conference(
            jid: $jid,
            apiClient: $this->apiClient,
            sendXMPP: $this->sendXMPP
        );

        return $this->conferences[(string)$jid];
    }

    public function getConferenceBySFUJid(Jid $jid): ?Conference
    {
        foreach ($this->conferences as $conference) {
            if ($conference->getSFUJid() == $jid->bareJid()) {
                return $conference;
            }
        }

        return null;
    }

    public function getConference(Jid $jid): ?Conference
    {
        if (array_key_exists($jid->bareJid(), $this->conferences)) {
            return $this->conferences[$jid->bareJid()];
        }

        return null;
    }

    public function detroyConference(Jid $jid): bool
    {
        if (array_key_exists($jid->bareJid(), $this->conferences)) {
            unset($this->conferences[$jid->bareJid()]);
            return true;
        }

        return false;
    }
}
