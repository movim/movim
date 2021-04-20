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
        $bd = new Bundle;
        $bd->set($this->_to, $this->_id, $stanza->pubsub->items->item->bundle);

        $localBd = Bundle::where('user_id', $bd->user_id)
            ->where('bundle_id', $bd->bundle_id)
            ->where('jid', $bd->jid)
            ->first();

        // Only refresh if the bundle is different
        if (!$localBd || !$localBd->sameAs($bd)) {
            if ($localBd) {
                $bd->has_session = $localBd->has_session;
                $localBd->delete();
            }

            $bd->save();

            $this->pack($bd);
            $this->deliver();
        }
    }
}
