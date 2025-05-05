<?php

namespace Moxl\Xec\Action\ExtendedChannelSearch;

use Moxl\Xec\Action;
use Moxl\Stanza\ExtendedChannelSearch;

class Search extends Action
{
    protected ?string $_keyword;
    protected int $_max = 30;
    protected bool $_globalSearch = false;

    public function request()
    {
        $this->store();
        ExtendedChannelSearch::search($this->_keyword, $this->_max);
    }

    public function enableGlobalSearch()
    {
        $this->_globalSearch = true;
        return $this;
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

        $this->pack([
            'results' => $results,
            'global' => $this->_globalSearch,
            'keyword' => $this->_keyword,
            'total' => (int)$stanza->result?->set?->last
        ]);
        $this->deliver();
    }

    public function error(string $errorId, ?string $message = null)
    {
        $this->pack($this->_keyword);
        $this->deliver();
    }
}
