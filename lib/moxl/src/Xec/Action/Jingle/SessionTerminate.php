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

    public function handle($stanza, $parent = false)
    {
        $userid = \App\User::me()->id;
        $message = new \App\Message;
        $message->user_id = $userid;
        $message->id = 'm_' . generateUUID();
        $message->jidto = $userid;
        $message->jidfrom = current(explode('/', $this->_to));
        $message->published = gmdate('Y-m-d H:i:s');
        $message->thread = $this->_jingleSid;
        $message->type = 'jingle_end';
        $message->save();

        $this->event('jingle_sessionterminate', $this->_reason);

        $this->pack($message);
        $this->event('jingle_message');
    }

    public function setJingleSid($jingleSid)
    {
        $this->_jingleSid = $jingleSid;
        return $this;
    }

    public function setReason($reason)
    {
        if (in_array(
            $reason,
            [
                'success',
                'busy',
                'decline',
                'cancel',
                'unsupported-transports',
                'failed-transport',
                'unsupported-applications',
                'failed-application',
                'incompatible-parameters'
            ]
        )) {
            $this->_reason = $reason;
        }

        return $this;
    }
}
