<?php

namespace Moxl\Xec\Action\Version;

use Moxl\Xec\Action;
use Moxl\Stanza\Version;

class Send extends Action
{
    protected $_to;
    protected $_id;
    protected $_name;
    protected $_version;
    protected $_os;

    public function request()
    {
        Version::send($this->_to, $this->_id, $this->_name, $this->_version, $this->_os);
    }
}
