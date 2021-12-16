<?php

namespace Moxl\Xec\Action\Muc;

use Moxl\Xec\Action;
use Moxl\Stanza\Muc;
use App\Member;

class GetMembers extends Action
{
    protected $_to;
    private $lastStanzaId;

    public function request()
    {
        $this->lastStanzaId = \generateKey(6);

        $this->store();
        Muc::getMembers($this->_to, 'member');
        $this->store();
        Muc::getMembers($this->_to, 'outcast');
        $this->store();
        Muc::getMembers($this->_to, 'owner');
        $this->store($this->lastStanzaId);
        Muc::getMembers($this->_to, 'admin');
    }

    public function handle($stanza, $parent = false)
    {
        $i = 0;

        foreach ($stanza->query->item as $item) {
            if ($i == 0) {
                Member::where('conference', $this->_to)
                      ->where('affiliation', (string)$item->attributes()->affiliation)
                      ->delete();
            }

            $member = new Member;
            $member->conference = $this->_to;
            $member->jid = (string)$item->attributes()->jid;
            $member->affiliation = (string)$item->attributes()->affiliation;

            if ($item->attributes()->role) {
                $member->role = (string)$item->attributes()->role;
            }
            if ($item->attributes()->nick) {
                $member->nick = (string)$item->attributes()->nick;
            }

            $member->save();

            $i++;
        }
        // Only fire the request for the last one
        if ($stanza->attributes()->id == $this->lastStanzaId) {
            $this->deliver();
        }
    }
}
