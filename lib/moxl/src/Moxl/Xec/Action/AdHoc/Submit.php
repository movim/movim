<?php

namespace Moxl\Xec\Action\AdHoc;

use Moxl\Xec\Action;
use Moxl\Stanza\AdHoc;

class Submit extends Action
{
    protected $_to;
    protected $_node;
    protected $_data;
    protected $_sessionid;

    public function request()
    {
        $this->store();
        AdHoc::submit($this->_to, $this->_node, $this->_data, $this->_sessionid);
    }

    public function handle($stanza, $parent = false)
    {
        $this->prepare($stanza, $parent);
        $this->pack($stanza->command);
        $this->deliver();
    }

    public function error($errorid, $message)
    {
        $this->pack([
            'errorid' => $errorid,
            'message' => $message
        ]);
        $this->deliver();
    }
}
