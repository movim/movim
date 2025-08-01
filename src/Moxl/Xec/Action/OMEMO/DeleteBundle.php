<?php

namespace Moxl\Xec\Action\OMEMO;

use Moxl\Xec\Action;
use Moxl\Stanza\Pubsub;

class DeleteBundle extends Action
{
    protected $_id;

    public function request()
    {
        $this->store();
        Pubsub::delete(false, 'eu.siacs.conversations.axolotl.bundles:' . $this->_id);
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        me()->bundles()
                       ->where('jid', me()->id)
                       ->where('bundleid', $this->_id)
                       ->delete();

        $devicesList = array_values(me()->bundles()
            ->select('bundleid')
            ->where('jid', me()->id)
            ->pluck('bundleid')
            ->toArray());

        $sdl = new SetDeviceList;
        $sdl->setList($devicesList)
            ->request();
    }

    public function error(string $errorId, ?string $message = null)
    {
        $this->handle();
    }
}
