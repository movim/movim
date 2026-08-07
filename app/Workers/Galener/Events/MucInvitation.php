<?php

namespace App\Workers\Galener\Events;

use DOMDocument;
use Moxl\Stanza\Disco;

class MucInvitation extends Event
{
    public static function getHandlerPaths(): array
    {
        return ['message|x{http://jabber.org/protocol/muc#user}'];
    }

    public function handle(): ?DOMDocument
    {
        if ($this->node->stanza->x->invite) {
            $conference = $this->conferencesManager->createOrGetConference($this->node->from);
            $conference->join();

            return $this->iq(type: 'get', xml: Disco::request());
        }
        return null;
    }
}
