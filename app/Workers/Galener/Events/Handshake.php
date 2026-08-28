<?php

namespace App\Workers\Galener\Events;

use App\Info;
use App\Workers\Galener\Galener;
use Movim\Jid;
use Moxl\Stanza\ExternalServices;

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

        $infoSFU = Info::where('server', config('galener.xmpp_host'))->where('node', '')
            ->first();

        if ($infoSFU) {
            return $this->iq(
                type: 'get',
                xml: ExternalServices::request(),
                from: config('galener.xmpp_host'),
                to: $infoSFU->parent
            );
        }

        return null;
    }
}
