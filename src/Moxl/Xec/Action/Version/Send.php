<?php

namespace Moxl\Xec\Action\Version;

use Moxl\Stanza\Version;
use Moxl\Xec\Action;

class Send extends Action
{
    protected $_to;

    protected $_id;

    protected $_name;

    protected $_version;

    protected $_os;

    public function request()
    {
        $this->iq(
            Version::send($this->_name, $this->_version, $this->_os),
            to: $this->_to,
            id: $this->_id,
            type: 'result'
        );
    }
}
