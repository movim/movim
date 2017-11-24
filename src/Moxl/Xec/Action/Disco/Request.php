<?php

namespace Moxl\Xec\Action\Disco;

use Moxl\Xec\Action;
use Moxl\Stanza\Disco;
use Moxl\Xec\Action\Disco\Items;

class Request extends Action
{
    private $_node;
    private $_to;

    // Excluded nodes
    private $_excluded = [
        'http://www.android.com/gtalk/client/caps#1.1'
    ];

    public function request()
    {
        $this->store();

        if(!in_array($this->_node, $this->_excluded)) {
            Disco::request($this->_to, $this->_node);
        }
    }

    public function setNode($node)
    {
        $this->_node = $node;
        return $this;
    }

    public function setTo($to)
    {
        $this->_to = $to;
        return $this;
    }

    public function handle($stanza, $parent = false)
    {
        // Caps
        $c = new \Modl\Caps;

        if(isset($this->_node)) {
            $c->set($stanza, $this->_node);
        } else {
            $c->set($stanza, $this->_to);
        }

        $cd = new \Modl\CapsDAO;
        if(!empty($c->features)) {
            $cd->set($c);
        }

        // Info
        $ind = new \Modl\InfoDAO;
        $in = $ind->get($this->_to, $this->_node);

        if(!$in) {
            $in = new \Modl\Info;
        }

        $in->set($stanza);
        $ind->set($in);

        $this->pack([$this->_to, $this->_node]);
        $this->deliver();

        // Affiliations
        $affiliations = [];

        $owners = $stanza->query->xpath("//field[@var='pubsub#owner']/value/text()");
        if(!empty($owners)) {
            $affiliations['owner'] = [];
            foreach($owners as $owner) {
                array_push($affiliations['owner'], ['jid' => (string)$owner]);
            }
        }

        $publishers = $stanza->query->xpath("//field[@var='pubsub#publisher']/value/text()");
        if(!empty($publishers)) {
            $affiliations['publisher'] = [];
            foreach($publishers as $publisher) {
                array_push($affiliations['publisher'], ['jid' => (string)$publisher]);
            }
        }

        if(!empty($affiliations)) {
            $this->pack([
                'affiliations' => $affiliations,
                'server' => $this->_to,
                'node' => $this->_node
            ]);
            $this->method('affiliations');
            $this->deliver();
        }
    }
}
