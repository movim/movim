<?php


namespace Moxl\Xec\Payload;

class Displayed extends Payload
{
    public function handle($stanza, $parent = false)
    {
        $id = (string)$stanza->attributes()->id;

        $md = new \Modl\MessageDAO;
        $m = $md->getId($id);
        if($m) {
            $m->displayed = gmdate('Y-m-d H:i:s');
            $m->newid = $id;
            $md->set($m);

            $this->pack($m);
            $this->deliver();
        }
    }
}
