<?php

namespace Moxl\Xec\Action\Pubsub;

use App\Affiliation;
use Moxl\Stanza\Pubsub;
use Moxl\Xec\Action;

class SetAffiliations extends Action
{
    protected string $_to;
    protected string $_node;
    protected array $_data;

    public function request()
    {
        $this->store();
        $this->iq(Pubsub::setAffiliations($this->_node, $this->_data), to: $this->_to, type: 'set');
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $affiliations = Affiliation::where('server', $this->_to)
            ->where('node', $this->_node)
            ->get()
            ->keyBy('jid');

        foreach ($this->_data as $jid => $affiliation) {
            if ($affiliations->has($jid) && $affiliations->get($jid)->affiliation != $affiliation) {
                $updatedAffiliation = Affiliation::where('server', $this->_to)
                    ->where('node', $this->_node)
                    ->where('jid', $jid);

                if ($affiliation == 'none') {
                    $updatedAffiliation->delete();
                } else {
                    $updatedAffiliation->update(['affiliation' => $affiliation]);
                }
            }
        }

        $this->pack([
            'server' => $this->_to,
            'node' => $this->_node,
            'data' => $this->_data
        ]);
        $this->deliver();
    }
}
