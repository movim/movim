<?php

namespace Moxl\Xec\Action\Disco;

use Moxl\Xec\Action;
use Moxl\Xec\Action\Disco\Request;
use Moxl\Stanza\Disco;

class Items extends Action
{
    private $_to;

    public function request()
    {
        $this->store();
        Disco::items($this->_to);
    }

    public function setTo($to)
    {
        $this->_to = echapJid($to);
        return $this;
    }

    public function handle($stanza, $parent = false) {
        $nd = new \Modl\ItemDAO();

        $jid = null;

        foreach($stanza->query->item as $item) {
            $n = $nd->getItem($this->_to, (string)$item->attributes()->node);
            if(!$n) {
                $n = new \modl\Item();
            }

            $n->set($item, $this->_to);
            if(substr($n->node, 0, 29) != 'urn:xmpp:microblog:0:comments')
                $nd->set($n, true);

            if($n->jid != $jid) {
                $r = new Request;
                $r->setTo($n->jid)
                  ->request();
            }

            $jid = $n->jid;
        }

        $this->pack($this->_to);
        $this->deliver();
    }

    public function error($error) {
        $this->pack($this->_to);
        $this->deliver();
    }
}
