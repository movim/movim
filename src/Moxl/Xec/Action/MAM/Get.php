<?php

namespace Moxl\Xec\Action\MAM;

use Moxl\Xec\Action;
use Moxl\Stanza\MAM;

class Get extends Action {
    private $_jid;
    private $_start;
    private $_end;
    private $_limit;
    
    public function request() 
    {
        $this->store();
        MAM::get($this->_jid, $this->_start, $this->_end, $this->_limit);
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
    
    public function handle($stanza, $parent = false) {

    }
    
    public function error($error) {
  
    }

}
