<?php

namespace Moxl\Xec\Payload;

use App\Roster as DBRoster;
use App\User as DBUser;

class Roster extends Payload
{
    public function handle($stanza, $parent = false)
    {
        if (!$parent->attributes()->from
         || (current(explode('/', (string)$parent->attributes()->from)) != \App\User::me()->id)
        ) return;

        if ((string)$parent->attributes()->type == 'set') {
            $jid = current(explode('/', (string)$stanza->item->attributes()->jid));

            $contact = DBUser::me()->session->contacts()->where('jid', $jid)->first();

            if ($contact) {
                $contact->delete();
            }

            if ((string)$stanza->item->attributes()->subscription != 'remove') {
                $roster = DBRoster::firstOrNew(['jid' => $jid, 'session_id' => DBUser::me()->session->id]);
                $roster->set($stanza->item);
                $roster->save();
            }

            $this->pack($jid);
            $this->deliver();
        }
    }
}
