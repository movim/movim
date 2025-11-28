<?php

namespace Moxl\Xec\Payload;

use App\Member;
use App\Message;

class MucUser extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        if (isset($stanza->item)) {
            $from = bareJid((string)$parent->attributes()->from);
            $jid = bareJid((string)$stanza->item->attributes()->jid);

            if (empty($jid)) return;

            $member = Member::where('conference', $from)
                            ->where('jid', $jid)
                            ->first();

            if (!$member) {
                $member = new Member;
                $member->conference = $from;
                $member->jid = $jid;
            }

            // Only track changes
            if ($member->exists && $member->affiliation != (string)$stanza->item->attributes()->affiliation) {
                $message = Message::eventMessageFactory(
                    '',
                    bareJid((string)$from),
                    $jid
                );

                switch ((string)$stanza->item->attributes()->affiliation) {
                    case 'admin':
                        $message->type = 'muc_admin';
                        break;

                    case 'owner':
                        $message->type = 'muc_owner';
                        break;

                    case 'outcast':
                        $message->type = 'muc_outcast';
                        break;

                    case 'member':
                        $message->type = 'muc_member';
                        break;
                }

                if ($message->type != '') {
                    $message->save();

                    $this->pack($message);
                    $this->event('muc_event_message');
                }
            }

            $member->affiliation = (string)$stanza->item->attributes()->affiliation;
            $member->save();
        }
    }
}
