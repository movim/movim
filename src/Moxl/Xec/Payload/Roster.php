<?php

namespace Moxl\Xec\Payload;

use App\Roster as DBRoster;

class Roster extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        if (!$parent->attributes()->from
         || (bareJid((string)$parent->attributes()->from) != $this->me->id)
        ) return;

        if ((string)$parent->attributes()->type == 'set') {
            $jid = bareJid((string)$stanza->item->attributes()->jid);

            $contact = $this->me->session->contacts()->where('jid', $jid)->first();

            if ($contact) {
                $contact->delete();
            }

            if ((string)$stanza->item->attributes()->subscription != 'remove') {
                $roster = DBRoster::firstOrNew(['jid' => $jid, 'session_id' => $this->me->session->id]);

                if ($roster->set($this->me, $stanza->item)) {
                    $roster->upsert();
                }
            }

            $this->pack($jid);
            $this->deliver();
        }
    }
}
