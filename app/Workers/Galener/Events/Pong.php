<?php

namespace App\Workers\Galener\Events;

class Pong extends Event
{
    public static function getHandlerPaths(): array
    {
        return ['iq|ping{urn:xmpp:ping}'];
    }

    public function handle(): ?\DOMDocument
    {
        $conference = $this->conferencesManager->getConferenceBySFUJid($this->node->to);


        if ($conference) {
            if ($connection = $conference->getConnection($this->node->from)) {
                if ($this->node->stanza->error) {
                    $conference->removeConnection($this->node->from);
                    return null;
                } else {
                    $connection->xmppPong($this->node);
                }
            }
        } else {
            return $this->iq(type: 'error', error: 'service-unavailable');
        }

        return null;
    }
}
