<?php

namespace Moxl\Xec\Action\MAM;

use Moxl\Xec\Action;
use Moxl\Stanza\MAM;

use Movim\Session;

class Get extends Action
{
    private $_queryid;
    private $_to;
    private $_jid;
    private $_start;
    private $_end;
    private $_limit;

    public function request()
    {
        $sess = Session::start();

        // Generating the queryid key.
        $this->_queryid = \generateKey(12);
        $sess->set('mamid'.$this->_queryid, true);

        $this->store();

        MAM::get($this->_to, $this->_queryid, $this->_jid, $this->_start, $this->_end, $this->_limit);
    }

    public function setTo($to)
    {
        $this->_to = $to;
        return $this;
    }

    public function setJid($jid)
    {
        $this->_jid = $jid;
        return $this;
    }

    public function setStart($start)
    {
        $this->_start = $start;
        return $this;
    }

    public function setEnd($end)
    {
        $this->_end = $end;
        return $this;
    }

    public function setLimit($limit)
    {
        $this->_limit = $limit;
        return $this;
    }

    public function handle($stanza, $parent = false)
    {
        $sess = Session::start();
        $sess->remove('mamid'.$this->_queryid);
    }

    public function error($error) {

    }

}
