<?php

namespace Moxl\Xec\Action\Muc;

use Moxl\Xec\Action;
use Moxl\Stanza\Muc;

class ChangeAffiliation extends Action
{
    protected $_to;
    protected $_jid;
    protected $_affiliation;
    protected $_reason;

    private $_affiliations = ['owner', 'admin', 'member', 'outcast', 'none'];

    public function request()
    {
        $this->store();
        $this->iq(Muc::changeAffiliation($this->_jid, $this->_affiliation, $this->_reason), to: $this->_to, type: 'set');
    }

    public function setAffiliation(string $affiliation)
    {
        if (in_array($affiliation, $this->_affiliations)) {
            $this->_affiliation = $affiliation;
        }

        return $this;
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $this->pack($this->_affiliation);
        $this->deliver();
    }

    public function errorNotAllowed(string $errorId, ?string $message = null)
    {
        $this->deliver();
    }
}
