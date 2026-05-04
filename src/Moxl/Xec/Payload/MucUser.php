<?php

namespace Moxl\Xec\Payload;

use App\Member;
use App\Message;

class MucUser extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        // XEP-0463: MUC Affiliations Versioning
        if ($stanza->mav && (string)$stanza->mav->attributes()->xmlns == 'urn:xmpp:muc:affiliations:1') {
            $from = bareJid((string)$parent->attributes()->from);
            $members = [];

            foreach ($stanza->item as $item) {
                $member = new Member;
                $member->conference = $from;
                $member->jid = (string)$item->attributes()->jid;
                $member->affiliation = (string)$item->attributes()->affiliation;
                $member->role = (string)$item->attributes()->role ?? null;
                $member->nick = (string)$item->attributes()->nick ?? null;
                $member->version = (string)$stanza->mav->attributes()->until;

                array_push($members, $member->toArray());
            }

            Member::where('conference', $from)->delete();
            Member::saveMany($members);
            $this->deliver();
        } else if (isset($stanza->item)) {
            $from = bareJid((string)$parent->attributes()->from);
            $jid = bareJid((string)$stanza->item->attributes()->jid);

            if (empty($jid)) return;

            $member = Member::firstOrNew([
                'conference' => $from,
                'jid' => $jid
            ]);

            // Only track changes
            if ($member->exists && $member->affiliation != (string)$stanza->item->attributes()->affiliation) {
                $type = match ((string)$stanza->item->attributes()->affiliation) {
                    'admin' => 'muc_admin',
                    'owner' => 'muc_owner',
                    'outcast' => 'muc_outcast',
                    'member' => 'muc_member',
                    default => null,
                };

                if ($type != null) {
                    $message = Message::eventMessageFactory(
                        user: $this->me,
                        type: $type,
                        from: bareJid((string)$from),
                        thread: $jid
                    );
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
