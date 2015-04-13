<?php

namespace Moxl\Xec\Action\Muc;

use Moxl\Xec\Action;
use Moxl\Stanza\Muc;

class GetConfig extends Action
{
    private $_to;
    
    public function request() 
    {
        $this->store();
        Muc::getConfig($this->_to);
    }
    
    public function setTo($to)
    {
        $this->_to = $to;
        return $this;
    }
    
    public function handle($stanza, $parent = false) {
        $this->pack(array('config' => $stanza->query, 'room' => $this->_to));
        $this->deliver();
    }
    
    public function error($error) {
        
    }
}
