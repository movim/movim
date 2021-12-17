<?php

namespace Moxl\Xec\Payload;

use App\Member;

class MucUser extends Payload
{
    public function handle($stanza, $parent = false)
    {

        if (isset($stanza->item)) {
            $from = current(explode('/', (string)$parent->attributes()->from));
            $jid = current(explode('/', (string)$stanza->item->attributes()->jid));

            if (empty($jid)) return;

            $member = Member::where('conference', $from)
                            ->where('jid', $jid)
                            ->first();

            if (!$member) {
                $member = new Member;
                $member->conference = $from;
                $member->jid = $jid;
            }

            $member->affiliation = (string)$stanza->item->attributes()->affiliation;
            $member->save();
        }
    }
}
