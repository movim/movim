<?php

namespace Moxl\Xec\Action\Disco;

use Moxl\Xec\Action;
use Moxl\Xec\Action\Disco\Request;
use Moxl\Stanza\Disco;

class Items extends Action
{
    protected $_to;
    protected $_save = true;
    protected $_manual = false;

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

    public function enableManual()
    {
        $this->_manual = true;
        return $this;
    }

    public function disableSave()
    {
        $this->_save = false;
        return $this;
    }

    public function handle($stanza, $parent = false)
    {
        if ($this->_save) {
            if ($this->_manual) {
                $this->method('manual');
            }

            $parent = \App\Info::where('server', $this->_to)
                             ->where('node', '')
                             ->first();
            $counter = 0;

            foreach ($stanza->query->item as $item) {
                if ($this->_save) {
                    if ($item->attributes()->node) {
                        $info = \App\Info::firstOrNew([
                            'server' => $this->_to,
                            'node' => (string)$item->attributes()->node
                        ]);

                        if ($parent && $parent->isPubsubService()) {
                            $info->setPubsubItem($item);

                            if (!$info->isMicroblogCommentsNode()) {
                                $counter++;
                                $info->save();
                            }
                        }
                    } elseif ($parent && $parent->identities->contains('category', 'server')) {
                        $r = new Request;
                        $r->setTo((string)$item->attributes()->jid)
                          ->setParent($this->_to)
                          ->request();
                    }
                }
            }

            if ($parent && $parent->isPubsubService()) {
                $parent->occupants = $counter;
                $parent->save();
            }

            $this->pack($this->_to);
            $this->deliver();
        } else {
            $list = [];

            foreach ($stanza->query->item as $item) {
                $list[(string)$item->attributes()->jid] = (string)$item->attributes()->name;
            }

            if (count($list) > 0) {
                $this->pack($list);
                $this->method('nosave_handle');
                $this->deliver();
            } else {
                $this->method('nosave_error');
                $this->deliver();
            }
        }
    }

    public function error($error)
    {
        if ($this->_manual) {
            $this->method('manual_error');
        }

        $this->pack($this->_to);
        $this->deliver();
    }
}
