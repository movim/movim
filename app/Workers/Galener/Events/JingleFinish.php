<?php

namespace App\Workers\Galener\Events;

class JingleFinish extends Event
{
    public static function getHandlerPaths(): array
    {
        return ['message|finish{urn:xmpp:jingle-message:0}'];
    }

    public function handle(): ?\DOMDocument
    {
        $conference = $this->conferencesManager->getConferenceBySFUJid($this->node->to);

        if ($conference) {
            $conference->removeConnection($this->node->from);
            return $this->iq(type: 'result');
        } else {
            return $this->iq(type: 'error', error: 'service-unavailable');
        }
    }
}
