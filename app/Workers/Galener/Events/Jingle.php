<?php

namespace App\Workers\Galener\Events;

use DOMDocument;

class Jingle extends Event
{
    public static function getHandlerPaths(): array
    {
        return ['iq|jingle{urn:xmpp:jingle:1}'];
    }

    public function handle(): ?DOMDocument
    {
        $conference = $this->conferencesManager->getConferenceBySFUJid($this->node->to);

        if ($conference) {
            switch ((string)$this->node->stanza->jingle->attributes()->action) {
                case 'session-initiate':
                    if ($connection = $conference->getConnection($this->node->from)) {
                        $connection->xmppOfferOrScreenshare($this->node);
                    }
                    break;

                case 'session-terminate':
                    if ($connection = $conference->getConnection($this->node->from)) {
                        $connection->xmppScreenshareTerminate($this->node);
                    }
                    break;

                case 'session-info':
                    if ($connection = $conference->getConnection($this->node->from)) {
                        if (
                            $this->node->stanza->jingle->mute
                            && $this->node->stanza->jingle->mute->attributes()->xmlns == 'urn:xmpp:jingle:apps:rtp:info:1'
                        ) {
                            $connection->xmppMute($this->node);
                        }

                        if (
                            $this->node->stanza->jingle->unmute
                            && $this->node->stanza->jingle->unmute->attributes()->xmlns == 'urn:xmpp:jingle:apps:rtp:info:1'
                        ) {
                            $connection->xmppUnmute($this->node);
                        }
                    }
                    break;

                case 'content-accept':
                    if ($connection = $conference->getConnection($this->node->from)) {
                        $connection->xmppContentAccept($this->node);
                    }
                    break;

                case 'transport-info':
                    if ($connection = $conference->getConnection($this->node->from)) {
                        $connection->xmppCandidate($this->node);
                    }
                    break;
            }

            return $this->iq(type: 'result');
        } else {
            return $this->iq(type: 'error', error: 'service-unavailable');
        }
    }
}
