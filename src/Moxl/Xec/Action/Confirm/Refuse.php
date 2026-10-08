<?php

namespace Moxl\Xec\Action\Confirm;

use Moxl\Stanza\Confirm;
use Moxl\Xec\Action;

class Refuse extends Action
{
    protected $_to;
    protected $_id;
    protected $_url;
    protected $_method;

    public function request()
    {
        $this->store();
        Confirm::answer($this->_to, $this->_id, $this->_url, $this->_method, true);
    }
}
