<?php

namespace Moxl\Xec\Action\OMEMO;

use Moxl\Xec\Action;
use Moxl\Stanza\OMEMO;

use App\Bundle;

class GetBundle extends Action
{
    protected $_to;
    protected $_id;

    public function request()
    {
        $this->store();
        OMEMO::getBundle(
            $this->_to,
            $this->_id
        );
    }

    public function handle($stanza, $parent = false)
    {
        $bundle = Bundle::where('user_id', \App\User::me()->id)
            ->where('jid', $this->_to)
            ->where('bundle_id', $this->_id)
            ->first();

        if (!$bundle) {
            $bundle = new Bundle;
        }

        $oldBundle = clone $bundle;

        $bundle->set($this->_to, $this->_id, $stanza->pubsub->items->item->bundle);

        // Only refresh if the bundle is different
        if (!$oldBundle->sameAs($bundle)) {
            $bundle->save();

            $this->pack($bundle);
            $this->deliver();
        }
    }
}
