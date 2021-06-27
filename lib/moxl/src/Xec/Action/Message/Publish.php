<?php

namespace Moxl\Xec\Action\Message;

use App\MessageOmemoHeader;
use Moxl\Xec\Action;
use Moxl\Stanza\Message;
use Moxl\Stanza\Muc;

class Publish extends Action
{
    protected $_to;
    protected $_content;
    protected $_html;
    protected $_muc = false;
    protected $_mucreceipts = false;
    protected $_id = false;
    protected $_replace = false;
    protected $_file = false;
    protected $_attachid = false;
    protected $_originid = false;
    protected $_threadid = false;

    // OMEMO
    protected $_messageOMEMO;

    public function request()
    {
        $this->store($this->_id);
        if ($this->_muc) {
            Muc::message($this->_to, $this->_content, $this->_html, $this->_id,
                         $this->_file, $this->_attachid, [], $this->_originid,
                         $this->_threadid, $this->_mucreceipts);
        } else {
            Message::message($this->_to, $this->_content, $this->_html, $this->_id,
                             $this->_replace, $this->_file, $this->_attachid, [],
                             $this->_originid, $this->_threadid, $this->_messageOMEMO);
        }
    }

    public function setMuc()
    {
        $this->_muc = true;
        return $this;
    }

    public function setMessageOMEMO(MessageOmemoHeader $messageOMEMO)
    {
        $this->_messageOMEMO = $messageOMEMO;
    }

    public function setMucReceipts()
    {
        $this->_mucreceipts = true;
        return $this;
    }

    public function getMuc()
    {
        return $this->_muc;
    }

    public function handle($stanza, $parent = false)
    {
        if ($this->_muc) {
            $m = new \Moxl\Xec\Payload\Message;
            $m->handle($stanza, $parent);
        }
    }

    public function error($id, $message = '')
    {
        $this->pack($message);
        $this->deliver();
    }
}
