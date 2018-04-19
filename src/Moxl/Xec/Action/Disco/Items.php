<?php

namespace Moxl\Xec\Action\Disco;

use Moxl\Xec\Action;
use Moxl\Xec\Action\Disco\Request;
use Moxl\Stanza\Disco;

class Items extends Action
{
    private $_to;

    public function request()
    {
        $this->store();
        Disco::items($this->_to);
    }

    public function setTo($to)
    {
        $this->_to = echapJid($to);
        return $this;
    }

    public function handle($stanza, $parent = false)
    {
        $jid = null;

        $parent = \App\Info::where('server', $this->_to)
                         ->where('node', '')
                         ->first();
        $counter = 0;

        foreach ($stanza->query->item as $item) {
            $info = \App\Info::firstOrNew([
                                    'server' => $this->_to,
                                    'node' => (string)$item->attributes()->node
                                ]);

            $info->setItem($item);

            if ($parent && $parent->isPubsubService()) {
                $info->category = 'pubsub';

                if (!$info->isMicroblogCommentsNode()) {
                    $counter++;
                }
            }

            if (!empty($info->category)
            && !$info->isMicroblogCommentsNode()) {
                $info->save();
            }

            if ($jid != $info->server) {
                if (isset($info->node)
                && $info->node != ''
                && $info->node != 'urn:xmpp:microblog:0') {
                    $r = new Request;
                    $r->setTo($info->server)
                      ->setNode($info->node)
                      ->request();
                }

                if (strpos($info->server, '/') === false) {
                    $r = new Request;
                    $r->setTo($info->server)
                      ->request();
                }
            }

            $jid = $info->server;
        }

        if ($parent && $parent->isPubsubService()) {
            $parent->occupants = $counter;
            $parent->save();
        }

        $this->pack($this->_to);
        $this->deliver();
    }

    public function error($error)
    {
        $this->pack($this->_to);
        $this->deliver();
    }
}
