<?php

namespace Moxl\Xec\Action\Muc;

use App\Member;
use Moxl\Stanza\Muc;
use Moxl\Xec\Action;

class GetAffiliations extends Action
{
    protected string $_to;
    private string $lastStanzaId;

    public function request()
    {
        $this->lastStanzaId = \generateKey(6);

        $this->store();
        $this->iq(Muc::getAffiliations('member'), to: $this->_to, type: 'get');
        $this->store();
        $this->iq(Muc::getAffiliations('outcast'), to: $this->_to, type: 'get');
        $this->store();
        $this->iq(Muc::getAffiliations('owner'), to: $this->_to, type: 'get');
        $this->store($this->lastStanzaId);
        $this->iq(Muc::getAffiliations('admin'), to: $this->_to, type: 'get');
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $i = 0;
        $members = [];
        $membersJid = [];

        foreach ($stanza->query->item as $item) {
            $member = new Member;
            $member->conference = $this->_to;
            $member->jid = (string) $item->attributes()->jid;
            $member->affiliation = (string) $item->attributes()->affiliation;
            $member->role = $item->attributes()->role
                ? (string) $item->attributes()->role
                : null;
            $member->nick = $item->attributes()->nick
                ? (string) $item->attributes()->nick
                : null;

            $i++;

            array_push($membersJid, $member->jid);
            array_push($members, $member->toArray());
        }

        Member::where('conference', $this->_to)
            ->whereIn('jid', $membersJid)
            ->delete();
        Member::saveMany($members);

        // Only fire the request for the last one
        if ($stanza->attributes()->id == $this->lastStanzaId) {
            $this->deliver();
        }
    }
}
