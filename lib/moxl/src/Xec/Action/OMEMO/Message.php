<?php

namespace Moxl\Xec\Action\OMEMO;

use Moxl\Xec\Action;
use Moxl\Stanza\OMEMO;

class Message extends Action
{
    private $_to;
    private $_sid;
    private $_keys;
    private $_iv;
    private $_payload;

    public function request()
    {
        $this->store();
        OMEMO::message(
            $this->_to,
            $this->_sid,
            $this->_keys,
            $this->_iv,
            $this->_payload,
        );
    }

    public function setTo($to)
    {
        $this->_to = $to;
        return $this;
    }

    public function setSid($sid)
    {
        $this->_sid = $sid;
        return $this;
    }

    public function setKeys($keys)
    {
        $this->_keys = $keys;
        return $this;
    }

    public function setIv($iv)
    {
        $this->_iv = $iv;
        return $this;
    }

    public function setPayload($payload)
    {
        $this->_payload = $payload;
        return $this;
    }
}
