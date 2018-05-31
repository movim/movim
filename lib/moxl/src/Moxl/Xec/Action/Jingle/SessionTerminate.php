<?php

namespace Moxl\Xec\Action\Jingle;

use Moxl\Xec\Action;
use Moxl\Stanza\Jingle;

class SessionTerminate extends Action
{
    protected $_to;
    protected $_jingleSid;
    protected $_reason = 'success';

    public function request()
    {
        $this->store();
        Jingle::sessionTerminate($this->_to, $this->_jingleSid, $this->_reason);
    }

    public function setJingleSid($jingleSid)
    {
        $this->_jingleSid = $jingleSid;
        return $this;
    }

    public function setReason($reason)
    {
        if(in_array($reason,
            [
                'success',
                'busy',
                'decline',
                'unsupported-transports',
                'failed-transport',
                'unsupported-applications',
                'failed-application',
                'incompatible-parameters'
            ]))
            $this->_reason = $reason;

        return $this;
    }

    public function handle($stanza, $parent = false)
    {
    }
}

