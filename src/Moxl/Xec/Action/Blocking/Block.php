<?php

namespace Moxl\Xec\Action\Blocking;

use App\Reported;
use Moxl\Stanza\Blocking;
use Moxl\Xec\Action;

class Block extends Action
{
    protected $_jid;

    public function request()
    {
        $this->store();
        $this->iq(Blocking::block($this->_jid), type: 'set');
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $r = Reported::firstOrCreate(['id' => $this->_jid]);
        $this->me->reported()->syncWithoutDetaching([$r->id => ['synced' => true]]);
        $this->me->refreshBlocked();

        $this->pack($this->_jid);
        $this->deliver();
    }
}
