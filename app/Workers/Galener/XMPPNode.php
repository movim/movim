<?php

namespace App\Workers\Galener;

use Movim\Jid;
use SimpleXMLElement;

class XMPPNode
{
    public SimpleXMLElement $stanza;
    public Jid $from;
    public Jid $to;
    public string $type;
    public ?string $id;

    public function __construct(SimpleXMLElement $node)
    {
        $this->stanza = $node;

        if ($from = $node->attributes()->{'from'}) {
            $this->from = new Jid((string)$from);
        }

        if ($to = $node->attributes()->{'to'}) {
            $this->to = new Jid((string)$to);
        }

        if ($id = $node->attributes()->{'id'}) {
            $this->id = (string)$id;
        }

        $this->type = $node->attributes()->{'type'};
    }
}
