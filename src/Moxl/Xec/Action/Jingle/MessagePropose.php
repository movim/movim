<?php

namespace Moxl\Xec\Action\Jingle;

use Moxl\Xec\Action;
use Moxl\Stanza\Jingle;

class MessagePropose extends Action
{
    protected $_to;
    protected $_id;
    protected bool $_withVideo = false;

    public function request()
    {
        $this->store();
        Jingle::messagePropose($this->_to, $this->_id, $this->_withVideo);
    }

    public function setWithVideo(bool $withVideo)
    {
        $this->_withVideo = $withVideo;
        return $this;
    }
}
