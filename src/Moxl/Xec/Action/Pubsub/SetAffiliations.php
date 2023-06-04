<?php

namespace Moxl\Xec\Action\Pubsub;

use Moxl\Stanza\Pubsub;
use Moxl\Xec\Action;

class SetAffiliations extends Action
{
    protected $_to;
    protected $_node;
    protected $_data;

    public function request()
    {
        $this->store();
        Pubsub::setAffiliations($this->_to, $this->_node, $this->_data);
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $ga = new GetAffiliations;
        $ga->setTo($this->_to)
           ->setNode($this->_node)
           ->request();

        $this->deliver();
    }
}
