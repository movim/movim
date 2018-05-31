<?php

namespace Moxl\Xec\Action\Message;

use Moxl\Xec\Action;
use Moxl\Stanza\Message;
use Moxl\Stanza\Muc;

class Publish extends Action
{
    protected $_to;
    protected $_content;
    protected $_html;
    protected $_muc = false;
    protected $_encrypted = false;
    protected $_id = false;
    protected $_replace = false;
    protected $_file = false;

    public function request()
    {
        $this->store();
        if($this->_muc) {
            Muc::message($this->_to, $this->_content, $this->_html, $this->_id, $this->_file);
        } elseif($this->_encrypted) {
            Message::encrypted($this->_to, $this->_content, $this->_html, $this->_id, $this->_replace);
        } else {
            Message::message($this->_to, $this->_content, $this->_html, $this->_id, $this->_replace, $this->_file);
        }
    }

    public function setMuc()
    {
        $this->_muc = true;
        return $this;
    }

    public function getMuc()
    {
        return $this->_muc;
    }

    public function handle($stanza, $parent = false)
    {
        if($this->_muc) {
            $m = new \Moxl\Xec\Payload\Message;
            $m->handle($stanza, $parent);
        }
    }
}
