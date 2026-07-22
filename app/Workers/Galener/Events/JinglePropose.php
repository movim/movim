<?php

namespace App\Workers\Galener\Events;

use Moxl\Stanza\Jingle;

class JinglePropose extends Event
{
    public static function getHandlerPath(): string
    {
        return 'message|propose{urn:xmpp:jingle-message:0}';
    }

    public function handle(): ?\DOMDocument
    {
        $conference = $this->conferencesManager->getConference($this->node->to->username);
        $conference->addConnection($this->node->from);

        return Jingle::messageProceed(
            to: $this->node->from,
            from: $this->node->to,
            id: (string)$this->node->stanza->propose->attributes()->id
        );
    }
}
