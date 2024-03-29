<?php

namespace Moxl\Xec\Action\Muclumbus;

use Moxl\Xec\Action;
use Moxl\Stanza\Muclumbus;

class Search extends Action
{
    protected $_keyword;

    public function request()
    {
        $this->store();
        Muclumbus::search($this->_keyword);
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $results = [];

        foreach ($stanza->result->item as $item) {
            array_push($results, [
                'jid' => (string)$item->attributes()->address,
                'name' => (string)$item->name,
                'description' => (string)$item->description,
                'occupants' => (string)$item->nusers,
                'public' => (bool)$item->{'is-open'},
            ]);
        }

        $this->pack($results);
        $this->deliver();
    }

    public function error(string $errorId, ?string $message = null)
    {
        $this->pack($this->_keyword);
        $this->deliver();
    }
}
