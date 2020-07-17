<?php

namespace Moxl\Xec\Action\Storage;

use Moxl\Xec\Action;
use Moxl\Stanza\Storage;

use App\User;

class Get extends Action
{
    protected $_xmlns;

    public function request()
    {
        $this->store();
        Storage::get($this->_xmlns);
    }

    public function handle($stanza, $parent = false)
    {
        if ($stanza->query->data) {
            $data = unserialize(trim((string)$stanza->query->data));

            if (is_array($data)) {
                $me = User::me();
                $me->setConfig($data);
                $me->save();
            }

            $this->pack($data);
            $this->deliver();
        }
    }

    public function error($stanza, $parent = false)
    {
        $this->deliver();
    }
}
