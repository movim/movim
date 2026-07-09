<?php

namespace App\Workers\Galener\Events;

use Moxl\Stanza\Jingle;

class JingleFinish extends Event
{
    public static function getHandlerPaths(): array
    {
        return ['message|finish{urn:xmpp:jingle-message:0}'];
    }

    public function handle(): ?\DOMDocument
    {
        $conference = $this->conferencesManager->getConference($this->node->to/*->username*/);
        $conference->removeConnection($this->node->from);

        return null;/*Jingle::messageFinish(
            to: $this->node->from,
            id: (string)$this->node->stanza->finish->attributes()->id
        );*/
    }
}
