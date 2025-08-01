<?php

namespace Moxl\Xec\Action\Blocking;

use App\Reported;
use Moxl\Stanza\Blocking;
use Moxl\Xec\Action;

class Request extends Action
{
    public function request()
    {
        $this->store();
        Blocking::request();
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $jids = collect();

        foreach ($stanza->blocklist->item as $item) {
            $jids->push((string)$item->attributes()->jid);
        }

        Reported::insert($jids->diff(Reported::whereIn('id', $jids)->get()->pluck('id'))->map(function ($jid) {
            $now = \Carbon\Carbon::now();
            return [
                'id' => $jid,
                'created_at' => $now,
                'updated_at' => $now
            ];
        })->toArray());

        me()->reported()->syncWithoutDetaching($jids->mapWithKeys(function ($jid) {
            return [$jid  => ['synced' => true]];
        }));
        me()->refreshBlocked();

        // Retro-compatibility support
        foreach (me()->reported()->where('synced', false)->get() as $reported) {
            $block = new Block;
            $block->setJid($reported->id);
            $block->request();
        }

        $this->pack($jids);
        $this->deliver();
    }
}
