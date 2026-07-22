<?php

namespace App\Workers\Galener\Events;

use DOMDocument;

class Jingle extends Event
{
    public static function getHandlerPath(): string
    {
        return 'iq|jingle{urn:xmpp:jingle:1}';
    }

    public function handle(): ?DOMDocument
    {
        $conference = $this->conferencesManager->getConference($this->node->to->username);

        switch ((string)$this->node->stanza->jingle->attributes()->action) {
            case 'session-initiate':
                if ($connection = $conference->getConnection($this->node->from)) {
                    $connection->xmppOffer($this->node);
                }
                break;

            case 'transport-info':
                if ($connection = $conference->getConnection($this->node->from)) {
                    $connection->xmppCandidate($this->node);
                }
                break;
        }

        return $this->iq(type: 'result');
    }
}
