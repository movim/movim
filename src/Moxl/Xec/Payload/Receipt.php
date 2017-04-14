<?php


namespace Moxl\Xec\Payload;

class Receipt extends Payload
{
    public function handle($stanza, $parent = false)
    {
        $from = (string)$parent->attributes()->from;
        $id = (string)$parent->attributes()->id;

        \Moxl\Stanza\Message::receipt($from, $id);

        $md = new \Modl\MessageDAO;
        $m = $md->getId($id);

        if($m) {
            $m->delivered = gmdate('Y-m-d H:i:s');
            $m->newid = $id;
            $md->set($m);

            $this->pack($m);
            $this->deliver();
        }
    }
}
