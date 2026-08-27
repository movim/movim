<?php

namespace App\Workers\Galener\Events;

use App\Workers\Galener\Galener;
use Movim\Jid;

class Handshake extends Event
{
    public static function getHandlerPaths(): array
    {
        return ['handshake'];
    }

    public function handle(): ?\DOMDocument
    {
        // Load the configured Conferences
        foreach (glob(Galener::CONFERENCES_CACHE . '*.json') as $conferenceFile) {
            $jid = new Jid(pathinfo($conferenceFile, PATHINFO_FILENAME));

            if ($jid->isValid()) {
                $conference = $this->conferencesManager->createOrGetConference($jid);
                $conference->xmppJoin();
            }
        }

        return null;
    }
}
