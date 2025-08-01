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
        Blocking::block($this->_jid);
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $r = Reported::firstOrCreate(['id' => $this->_jid]);
        me()->reported()->syncWithoutDetaching([$r->id => ['synced' => true]]);
        me()->refreshBlocked();

        $this->pack($this->_jid);
        $this->deliver();
    }
}
