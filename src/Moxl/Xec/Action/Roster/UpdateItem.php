<?php

namespace Moxl\Xec\Action\Roster;

use Moxl\Xec\Action;
use Moxl\Stanza\Roster;

class UpdateItem extends Action
{
    protected $_to;
    protected $_from;
    protected $_name;
    protected $_group;

    public function request()
    {
        $this->store();
        $this->iq(Roster::update($this->_to, $this->_name, $this->_group), type: 'set');
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $roster = $this->me->session->contacts()->where('jid', $this->_to)->first();

        if ($roster) {
            $roster->name = $this->_name;
            $roster->group = $this->_group;
            $roster->upsert();

            $this->pack($this->_to);
            $this->deliver();
        }
    }
}
