<?php

namespace App\Workers\Galener\Events;

use Moxl\Stanza\Jingle;
use Moxl\Utils;

class JinglePropose extends Event
{
    public static function getHandlerPaths(): array
    {
        return ['message|propose{urn:xmpp:jingle-message:0}'];
    }

    public function handle(): ?\DOMDocument
    {
        $conference = $this->conferencesManager->getConferenceBySFUJid($this->node->to);

        if (! $conference || ! $conference->addConnection($this->node->from)) {
            return Utils::removeNamespace(Jingle::messageReject(
                to: $this->node->from,
                from: $this->node->to,
                id: (string) $this->node->stanza->propose->attributes()->id,
                reasonText: 'Galener: propose from '.(string) $this->node->from.' had no matching member/conference yet'
            ));
        }

        return Utils::removeNamespace(Jingle::messageProceed(
            to: $this->node->from,
            from: $this->node->to,
            id: (string) $this->node->stanza->propose->attributes()->id
        ));
    }
}
