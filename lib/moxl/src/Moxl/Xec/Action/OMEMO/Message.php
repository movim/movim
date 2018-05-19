<?php

namespace Moxl\Xec\Action\OMEMO;

use Moxl\Xec\Action;
use Moxl\Stanza\OMEMO;

class Message extends Action
{
    private $_to;
    private $_sid;
    private $_key;
    private $_rid;
    private $_iv;
    private $_payload;
    private $_isprekey;

    public function request()
    {
        $this->store();
        OMEMO::message(
            $this->_to,
            $this->_sid,
            $this->_key,
            $this->_rid,
            $this->_iv,
            $this->_payload,
            $this->_isprekey
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

    public function setKey($key)
    {
        $this->_key = $key;
        return $this;
    }

    public function setRid($rid)
    {
        $this->_rid = $rid;
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

    public function setIsPreKey($isprekey)
    {
        $this->_isprekey = $isprekey;
        return $this;
    }

    public function handle($stanza, $parent = false)
    {
    }
}
