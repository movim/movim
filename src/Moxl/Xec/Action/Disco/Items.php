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

    public function handle($stanza, $parent = false)
    {
        $id = new \Modl\InfoDAO;
        //$id->deleteItems($this->_to);

        $jid = null;

        foreach($stanza->query->item as $item) {
            $i = $id->get($this->_to, (string)$item->attributes()->node);

            if(!isset($i)) {
                $i = new \Modl\Info;
            }

            $i->setItem($item);

            if(substr($i->node, 0, 29) != 'urn:xmpp:microblog:0:comments') {
                $id->set($i);
            }

            if($jid != $i->server) {
                if(isset($i->node)
                && $i->node != ''
                && $i->node != 'urn:xmpp:microblog:0') {
                    $r = new Request;
                    $r->setTo($i->server)
                      ->setNode($i->node)
                      ->request();
                }

                if(strpos($i->server, '/') === false) {
                    $r = new Request;
                    $r->setTo($i->server)
                      ->request();
                }
            }

            $jid = $i->server;
        }

        $this->pack($this->_to);
        $this->deliver();
    }

    public function error($error)
    {
        $this->pack($this->_to);
        $this->deliver();
    }
}
