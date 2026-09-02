<?php

namespace App\Workers\Galener\Events;

use Movim\Jid;

class Presence extends Event
{
    public static function getHandlerPaths(): array
    {
        return ['presence'];
    }

    public function handle(): ?\DOMDocument
    {
        if ($conference = $this->conferencesManager->getConference($this->node->from)) {
            if ($results = $this->node->stanza->xpath('//x[@xmlns="http://jabber.org/protocol/muc#user"]/item')) {
                if (is_array($results)) {
                    $item = $results[0];
                    if ($this->node->to->bareJid() == config('galener.xmpp_host')) {
                        if (
                            in_array($item->attributes()->affiliation, ['owner', 'admin'])
                            && is_array($this->node->stanza->xpath('//status[@code=110]'))
                        ) {
                            $conference->xmppSetAdmin();
                        }
                    } else {
                        if (is_array($this->node->stanza->xpath('//status[@code=110]'))) {
                            // We are kicked out so we leave
                            if ($item->attributes()->affiliation == 'none') {
                                $conference->xmppLeaveAndDestroy();
                                $this->conferencesManager->detroyConference($this->node->from);
                            } else/*if (in_array($item->attributes()->affiliation, ['owner', 'admin']))*/ {
                                $conference->xmppAddMember(new Jid((string)$item->attributes()->jid));
                                /*} else {
                                $conference->xmppNotAdminMessage();*/
                                // For now ejabberd cannot allow the service to be admin https://github.com/processone/ejabberd/issues/4611
                            }
                        } else {
                            if ($this->node->type === 'unavailable') {
                                $conference->xmppRemoveMember(new Jid((string)$item->attributes()->jid));
                            } else {
                                $conference->xmppAddMember(new Jid((string)$item->attributes()->jid));
                            }
                        }
                    }
                }
            }
        }

        return null;
    }
}
