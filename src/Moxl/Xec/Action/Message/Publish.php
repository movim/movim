<?php

namespace Moxl\Xec\Action\Message;

use App\MessageFile;
use Moxl\Xec\Action;
use Moxl\Stanza\Message;
use Moxl\Stanza\Muc;
use App\MessageOmemoHeader;

class Publish extends Action
{
    protected $_to;
    protected $_content;
    protected $_html;
    protected $_muc = false;
    protected $_mucreceipts = false;
    protected $_id = false;
    protected $_replace = false;
    protected ?MessageFile $_file = null;
    protected $_attachid = false;
    protected $_originid = false;
    protected $_threadid = false;

    // Reply
    protected $_replyid = false;
    protected $_replyto = false;
    protected $_replyquotedbodylength = 0;

    // OMEMO
    protected $_messageOMEMO;

    public function request()
    {
        $this->store($this->_id);
        $this->send(Message::maker(
            to: $this->_to,
            type: $this->_muc ? 'groupchat' : 'chat',
            content: $this->_content,
            html: $this->_html,
            id: $this->_id,
            replace: $this->_replace,
            file: $this->_file,
            parentId: $this->_attachid,
            reactions: [],
            originId: $this->_originid,
            threadId: $this->_threadid,
            replyId: $this->_replyid,
            replyTo: $this->_replyto,
            replyQuotedBodyLength: $this->_replyquotedbodylength,
            messageOMEMO: $this->_messageOMEMO
        ));
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

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        if ($this->_muc) {
            $m = new \Moxl\Xec\Payload\Message($this->me);
            $m->handle($stanza, $parent);
        }
    }

    public function error(string $errorId, ?string $message = null)
    {
        $this->pack($message);
        $this->deliver();
    }
}
