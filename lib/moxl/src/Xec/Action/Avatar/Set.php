<?php

namespace Moxl\Xec\Action\Avatar;

use Moxl\Xec\Action;
use Moxl\Stanza\Avatar;

class Set extends Action
{
    protected $_data;
    protected $_to = false;
    protected $_node = false;
    protected $_url = false;
    protected $_widthMetadata = 350;
    protected $_heightMetadata = 350;

    public function request()
    {
        $this->store();

        if ($this->_url === false) {
            Avatar::set($this->_data, $this->_to, $this->_node);
        }

        Avatar::setMetadata($this->_data, $this->_url, $this->_to, $this->_node,
            $this->_widthMetadata, $this->_heightMetadata);
    }

    public function setWidthMetadata($width)
    {
        $this->_widthMetadata = $width;
        return $this;
    }

    public function setHeightMetadata($height)
    {
        $this->_heightMetadata = $height;
        return $this;
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        if ($this->_to == false && $this->_node == false) {
            $me = \App\User::me()->contact;
            $me->avatartype = 'urn:xmpp:avatar:metadata';
            $me->save();

            $this->pack($me);
            $this->deliver();
        } else {
            $this->method('pubsub');
            $this->pack(['to' => $this->_to, 'node' => $this->_node]);
            $this->deliver();
        }
    }

    public function errorFeatureNotImplemented(string $errorId, ?string $message = null)
    {
        $this->deliver();
    }

    public function errorBadRequest(string $errorId, ?string $message = null)
    {
        $this->deliver();
    }

    public function errorNotAllowed(string $errorId, ?string $message = null)
    {
        $this->deliver();
    }
}
