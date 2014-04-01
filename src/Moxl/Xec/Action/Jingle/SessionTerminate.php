<?php
/*
 * SessionTerminate.php
 * 
 * Copyright 2013 edhelas <edhelas@edhelas-laptop>
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 * 
 * 
 */

namespace Moxl\Xec\Action\Jingle;

use Moxl\Xec\Action;
use Moxl\Stanza;

class SessionTerminate extends Action
{
    private $_to;
    private $_jingleSid;
    private $_reason = 'success';
        
    public function request() 
    {
        $this->store();
        Stanza\jingleSessionTerminate($this->_to, $this->_jingleSid, $this->_reason);
    }
    
    public function setTo($to)
    {
        $this->_to = $to;
        return $this;
    }
    
    public function setJingleSid($jingleSid)
    {
        $this->_jingleSid = $jingleSid;
        return $this;
    }

    public function setReason($reason) {
        if(in_array($reason,
            array(
                'success',
                'busy',
                'decline',
                'unsupported-transports',
                'failed-transport',
                'unsupported-applications',
                'failed-application',
                'incompatible-parameters'
                ))
            )
            $this->_reason = $reason;

        return $this;
    }
    
    public function handle($stanza) {

    }
    
    public function error($error) {

    }
}


