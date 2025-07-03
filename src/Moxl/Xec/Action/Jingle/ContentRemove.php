<?php

namespace Moxl\Xec\Action\Jingle;

use Moxl\Xec\Action;
use Moxl\Stanza\Jingle;

class ContentRemove extends Action
{
    protected $_to;
    protected $_jingle;

    public function request()
    {
        $this->store();
        Jingle::contentAdd($this->_to, $this->_jingle);
    }
}
