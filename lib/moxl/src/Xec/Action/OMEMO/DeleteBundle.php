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

    public function handle($stanza, $parent = false)
    {
        \App\User::me()->bundles()
                       ->where('jid', \App\User::me()->id)
                       ->where('bundleid', $this->_id)
                       ->delete();

        $devicesList = array_values(\App\User::me()->bundles()
            ->select('bundleid')
            ->where('jid', \App\User::me()->id)
            ->pluck('bundleid')
            ->toArray());

        $sdl = new SetDeviceList;
        $sdl->setList($devicesList)
            ->request();
    }

    public function error($stanza, $parent = false)
    {
        $this->handle($stanza, $parent);
    }
}
