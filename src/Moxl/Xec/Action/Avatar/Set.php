<?php

namespace Moxl\Xec\Action\Avatar;

use Moxl\Xec\Action;
use Moxl\Stanza\Avatar;
use Moxl\Xec\Action\Pubsub\SetConfig;

class Set extends Action
{
    protected $_data;
    protected $_to = false;
    protected $_node = false;
    protected $_url = false;
    protected $_widthMetadata = 350;
    protected $_heightMetadata = 350;
    protected bool $_withPublishOption = true;

    public function request()
    {
        $this->store();

        if ($this->_url === false) {
            Avatar::set($this->_data, $this->_to, $this->_node, $this->_withPublishOption);
        } else {
            // For an URL we simply set the Metadata
            $setMetadata = new SetMetadata;
            $setMetadata->setTo($this->_to)
                ->setNode($this->_node)
                ->setUrl($this->_url)
                ->setData($this->_data)
                ->setWidthMetadata($this->_widthMetadata)
                ->setHeightMetadata($this->_heightMetadata)
                ->request();
        }
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
        $setMetadata = new SetMetadata;
        $setMetadata->setTo($this->_to)
            ->setNode($this->_node)
            ->setUrl($this->_url)
            ->setData($this->_data)
            ->setWidthMetadata($this->_widthMetadata)
            ->setHeightMetadata($this->_heightMetadata)
            ->request();

        if ($this->_to == false && $this->_node == false) {
            $me = me()->contact;
            $me->avatartype = Avatar::NODE_METADATA;
            $me->save();

            $this->deliver();
        } else {
            $this->method('pubsub');
            $this->pack(['to' => $this->_to, 'node' => $this->_node]);
            $this->deliver();
        }
    }

    public function errorPayloadTooBig(string $errorId, ?string $message = null)
    {
        $this->deliver();
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

    public function errorPreconditionNotMet(string $errorId, ?string $message = null)
    {
        $this->errorConflict($errorId, $message);
    }

    public function errorResourceConstraint(string $errorId, ?string $message = null)
    {
        $this->errorConflict($errorId, $message);
    }

    public function errorConflict(string $errorId, ?string $message = null)
    {
        $config = new SetConfig;
        $config->setNode(Avatar::NODE_DATA)
            ->setData(Avatar::$nodeConfig)
            ->request();

        $this->_withPublishOption = false;
        $this->request();
    }
}
