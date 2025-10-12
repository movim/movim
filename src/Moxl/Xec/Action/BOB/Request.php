<?php

namespace Moxl\Xec\Action\BOB;

use Moxl\Xec\Action;
use Moxl\Stanza\BOB;
use Movim\Image;

class Request extends Action
{
    protected $_to;
    protected $_hash;
    protected $_algorythm;
    protected $_resource;
    protected $_messagemid;
    private $_phpalgorythm;

    public function request()
    {
        $this->store();

        // Only request if the resource is available
        if (me()->session?->presences()->where('jid', $this->_to)->where('resource', $this->_resource)->exists()
        && $this->_algorythm) {
            BOB::request($this->_to . '/' . $this->_resource, $this->_hash, $this->_algorythm);
        }
    }

    public function setAlgorythm(string $algorythm)
    {
        $this->_phpalgorythm = $algorythm;
        $this->_algorythm = \phpToIANAHash()[$algorythm];
        return $this;
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $data = (string)$stanza->data;

        if (hash($this->_phpalgorythm, base64_decode($data)) == $this->_hash) {
            $p = new Image;
            $p->fromBase64($data);
            $p->setKey($this->_hash);
            $p->save();
        } else {
            $this->method('bad_data');
        }

        $this->pack(['to' => $this->_to, 'hash' => $this->_hash, 'algorythm' => $this->_algorythm]);
        $this->deliver();
    }
}
