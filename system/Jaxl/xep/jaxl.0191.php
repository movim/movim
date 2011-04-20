<?php
/**
 * Jaxl (Jabber XMPP Library)
 *
 * Copyright (c) 2009-2010, Abhinav Singh <me@abhinavsingh.com>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Abhinav Singh nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRIC
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package jaxl
 * @subpackage xep
 * @author Abhinav Singh <me@abhinavsingh.com>
 * @copyright Abhinav Singh
 * @link http://code.google.com/p/jaxl
 */
	
	/**
	 * XEP 0191 - Simple Communication Blocking
	*/
	class JAXL0191 {

        public static $ns = 'urn:xmpp:blocking';

        public static function init($jaxl) {
            $jaxl->features[] = self::$ns;
        }

        public static function getBlockList($jaxl, $callback) {
            $payload = '<blocklist xmlns="'.self::$ns.'"/>';
            return XMPPSend::iq($jaxl, 'get', $payload, false, false, $callback);
        }

        public static function blockContact($jaxl, $jid, $callback) {
            $payload = '<block xmlns="'.self::$ns.'">';
            if(!is_array($jid)) $jid = array($jid);
            foreach($jid as $item)
                $payload .= '<item jid="'.$item.'"/>';
            $payload .= '</block>';
            return XMPPSend::iq($jaxl, 'set', $payload, false, false, $callback);
        }

        public static function unblockContact($jaxl, $jid, $callback) {
            $payload = '<unblock xmlns="'.self::$ns.'">';
            if(!is_array($jid)) $jid = array($jid);
            foreach($jid as $item)
                $payload .= '<item jid="'.$item.'"/>';
            $payload .= '</unblock>';
            return XMPPSend::iq($jaxl, 'set', $payload, false, false, $callback);
        }

        public static function unblockAll($jaxl, $callback) {
            $payload = '<unblock xmlns="'.self::$ns.'"/>';
            return XMPPSend::iq($jaxl, 'set', $payload, false, false, $callback);
        }

	}
	
?>
