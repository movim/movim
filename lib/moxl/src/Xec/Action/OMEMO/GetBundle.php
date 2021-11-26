<?php

namespace Moxl\Xec\Action\OMEMO;

use Moxl\Xec\Action;
use Moxl\Stanza\OMEMO;
use App\Bundle;

class GetBundle extends Action
{
    protected $_to;
    protected $_id;
    protected $_notifyBundle = false;

    public function request()
    {
        $this->store();
        OMEMO::getBundle(
            $this->_to,
            $this->_id
        );
    }

    public function setNotifyBundle(bool $notifyBundle)
    {
        $this->_notifyBundle = $notifyBundle;
        return $this;
    }

    public function handle($stanza, $parent = false)
    {
        if ($stanza->pubsub->items->item->bundle) {
            $bundle = Bundle::where('user_id', \App\User::me()->id)
                ->where('jid', $this->_to)
                ->where('bundleid', $this->_id)
                ->first();

            if (!$bundle) {
                $bundle = new Bundle;
            }

            $oldBundle = clone $bundle;

            $bundle->set($this->_to, $this->_id, $stanza->pubsub->items->item->bundle);

            // Only refresh if the bundle is different
            if (!$oldBundle->sameAs($bundle)) {
                $bundle->save();

                if ($this->_notifyBundle) {
                    $this->pack($bundle);
                    $this->deliver();
                }
            }
        }
    }
}
