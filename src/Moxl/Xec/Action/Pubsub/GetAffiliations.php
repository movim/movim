<?php

namespace Moxl\Xec\Action\Pubsub;

use App\Affiliation;
use Moxl\Stanza\Pubsub;
use Moxl\Xec\Action;

class GetAffiliations extends Action
{
    protected $_to;
    protected $_node;

    public function request()
    {
        $this->store();
        $this->iq(Pubsub::getAffiliations($this->_node), to: $this->_to, type: 'get');
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        Affiliation::where('server', $this->_to)->where('node', $this->_node)->delete();

        foreach ($stanza->pubsub->affiliations->children() as $i) {
            $affiliation = new Affiliation;
            $affiliation->server = $this->_to;
            $affiliation->node = $this->_node;
            $affiliation->jid = (string)$i['jid'];
            $affiliation->affiliation = (string)$i['affiliation'];
            $affiliation->save();
        }

        $this->pack(['server' => $this->_to, 'node' => $this->_node]);
        $this->deliver();
    }

    public function error(string $errorId, ?string $message = null)
    {
        $this->pack(['server' => $this->_to, 'node' => $this->_node]);
        $this->deliver();
    }
}
