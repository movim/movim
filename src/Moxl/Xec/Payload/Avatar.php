<?php

namespace Moxl\Xec\Payload;

class Avatar extends Payload
{
    public function handle($stanza, $parent = false)
    {
        $jid = current(explode('/',(string)$parent->attributes()->from));

        $evt = new \Event;

        $cd = new \Modl\ContactDAO;
        $c = $cd->get($jid);

        if($c == null)
            $c = new \Modl\Contact;

        $p = new \Picture;
        $p->fromBase((string)$stanza->items->item->data);
        $p->set($jid);

        $evt->runEvent('vcard', $c);
    }
}
