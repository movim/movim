<?php

namespace Moxl\Xec\Action\Pubsub;

use Moxl\Stanza\Pubsub;
use Moxl\Xec\Action;

class GetConfig extends Action
{
    protected $_to;
    protected $_node;
    protected $_advanced = false;

    public function request()
    {
        $this->store();
        Pubsub::getConfig($this->_to, $this->_node);
    }

    public function enableAdvanced()
    {
        $this->_advanced = true;
        return $this;
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $value = $stanza->pubsub->configure->xpath('//field[@var=\'pubsub#access_model\']/value/text()');

        $accessModel = null;

        if (is_array($value) && count($value) > 0) {
            $accessModel = (string)$value[0];
        }

        $this->pack([
            'config' => $stanza->pubsub->configure,
            'access_model' => $accessModel,
            'server' => $this->_to,
            'node' => $this->_node,
            'advanced' => $this->_advanced
        ]);
        $this->deliver();
    }
}
