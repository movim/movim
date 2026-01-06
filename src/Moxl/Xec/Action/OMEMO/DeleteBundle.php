<?php

namespace Moxl\Xec\Action\OMEMO;

use App\Bundle;
use Moxl\Xec\Action;
use Moxl\Stanza\Pubsub;

class DeleteBundle extends Action
{
    protected $_id;
    protected array $_devicesIds;

    public function request()
    {
        $this->store();
        $this->iq(Pubsub::delete(Bundle::OMEMO_BUNDLE . $this->_id), type: 'set');
    }

    public function setDevicesIds(array $devicesIds)
    {
        $this->_devicesIds = $devicesIds;
        return $this;
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        if (array_search($this->_id, $this->_devicesIds) !== false) {
            unset($this->_devicesIds[array_search($this->_id, $this->_devicesIds)]);
        }

        $sdl = new SetDevicesList($this->me);
        $sdl->setList($this->_devicesIds)
            ->request();
    }

    public function error(string $errorId, ?string $message = null)
    {
        $this->handle();
    }
}
