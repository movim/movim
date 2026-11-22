<?php

namespace Moxl\Xec\Action\Avatar;

use Moxl\Stanza\Avatar;
use Moxl\Xec\Action;
use Moxl\Xec\Action\Pubsub\SetConfig;

class SetMetadata extends Action
{
    protected ?string $_data;
    protected ?string $_to = null;
    protected ?string $_node = null;
    protected ?string $_itemIdMetadata = null;
    protected ?string $_url = null;
    protected int $_widthMetadata = 350;
    protected int $_heightMetadata = 350;
    protected bool $_withPublishOption = true;

    public function request()
    {
        $this->store();
        $this->iq(Avatar::setMetadata(
            data: $this->_data,
            url: $this->_url,
            node: $this->_node,
            itemId: $this->_itemIdMetadata,
            width: $this->_widthMetadata,
            height: $this->_heightMetadata,
            withPublishOption: $this->_withPublishOption
        ), to: $this->_to, type: 'set');
    }

    public function setWidthMetadata(int $width)
    {
        $this->_widthMetadata = $width;
        return $this;
    }

    public function setHeightMetadata(int $height)
    {
        $this->_heightMetadata = $height;
        return $this;
    }

    public function setItemIdMetadata(?string $itemId = null)
    {
        $this->_itemIdMetadata = $itemId;
        return $this;
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        // And we re-request the server to refresh things just in case...
        $get = new Get($this->me, sessionId: $this->sessionId);
        $get->setTo($this->_to)
            ->setNode($this->_node)
            ->request();

        $this->pack(['to' => $this->_to, 'node' => $this->_node]);
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
        $config = new SetConfig($this->me, sessionId: $this->sessionId);
        $config->setNode(Avatar::NODE_METADATA)
            ->setData(Avatar::$nodeConfig)
            ->request();

        $this->_withPublishOption = false;
        $this->request();
    }
}
