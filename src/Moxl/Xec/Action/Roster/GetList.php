<?php

namespace Moxl\Xec\Action\Roster;

use Moxl\Xec\Action;
use Moxl\Stanza\Roster;
use App\Roster as DBRoster;

class GetList extends Action
{
    public function request()
    {
        $this->store();
        $this->iq(Roster::get(), type: 'get');
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $rosters = [];

        foreach ($stanza->query->item as $item) {
            $roster = new DBRoster;

            if ($roster->set($this->me, $item)) {
                array_push($rosters, $roster->toArray());
            }
        }

        DBRoster::where('session_id', $this->me->session->id)->delete();
        DBRoster::saveMany($rosters);

        $this->deliver();
    }
}
