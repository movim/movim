<?php

namespace Moxl\Xec\Action\MAM;

use App\MAMEarliest;
use Moxl\Xec\Action;
use Moxl\Stanza\MAM;
use Movim\Session;
//use App\MessageBuffer;

class Get extends Action
{
    protected ?string $_to = null;
    protected ?string $_queryid = null;
    protected ?string $_jid = null;
    protected ?int $_start = null;
    protected ?int $_end = null;
    protected ?int $_limit = null;
    protected ?string $_after = null;
    protected ?string $_before = null;
    protected string $_version = '2';
    protected int $_messageCounter = 0;

    public function request()
    {
        $session = Session::instance();

        // Generating the queryid key.
        $this->_queryid = \generateKey(12);
        $session->set('mamid' . $this->_queryid, 0);
        $this->store();

        MAM::get(
            $this->_to,
            $this->_queryid,
            $this->_jid,
            $this->_start,
            $this->_end,
            $this->_limit,
            $this->_after,
            $this->_before,
            $this->_version
        );
    }

    public function setMessageCounter(int $messagesCounter)
    {
        $this->_messageCounter = $messagesCounter;
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        //MessageBuffer::getInstance()->save();

        $session = Session::instance();

        $messagesCounter = (int)$session->get('mamid' . $this->_queryid);
        $this->pack(['counter' => $messagesCounter, 'forward' => ($this->_start != null)]);

        $session->delete('mamid' . $this->_queryid);
        $this->deliver();

        $totalCounter = $this->_messageCounter + $messagesCounter;

        if (
            isset($stanza->fin->set)
            && $stanza->fin->set->attributes()->xmlns == 'http://jabber.org/protocol/rsm'
            && $stanza->fin->set->count == 0
            && (
                ($this->_end != null && $this->_start == null)
                ||
                ($this->_end == null && $this->_start == null && $this->_before == '')
            )
        ) {
            $earliest = new MAMEarliest;
            $earliest->user_id = me()->id;
            $earliest->to = $this->_to;
            $earliest->jid = $this->_jid;
            $earliest->earliest = date(MOVIM_SQL_DATE, $this->_end ?? time());
            $earliest->save();
        }

        if (
            isset($stanza->fin)
            && (!isset($stanza->fin->attributes()->complete) || $stanza->fin->attributes()->complete != 'true')
            && isset($stanza->fin->set) && $stanza->fin->set->attributes()->xmlns == 'http://jabber.org/protocol/rsm'
            && isset($stanza->fin->set->last)
            && $this->_after != (string)$stanza->fin->set->last
            && $totalCounter < $this->_limit
        ) {
            $g = new Get;
            $g->setJid($this->_jid);
            $g->setTo($this->_to);
            $g->setLimit($this->_limit);
            $g->setStart($this->_start);
            $g->setEnd($this->_end);
            $g->setBefore($this->_before);
            $g->setVersion($this->_version);
            $g->setAfter((string)$stanza->fin->set->last);
            $g->setMessageCounter($totalCounter);
            $g->request();
        }
    }
}
