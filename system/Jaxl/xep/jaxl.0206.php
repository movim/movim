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
     * XEP-0206: XMPP over BOSH
     * 
     * Uses XEP-0124 to wrap XMPP stanza's inside <body/> wrapper
    */
    class JAXL0206 {
        
        public static function init($jaxl) {
            $jaxl->log("[[JaxlAction]] ".$_REQUEST['jaxl']."\n".json_encode($_REQUEST), 5);
            
            // Requires Bosh Session Manager
            $jaxl->requires('JAXL0124');
        }
        
        public static function jaxl($jaxl, $xml) { 
            $jaxl->sendXML(urldecode($xml));
        }

        public static function startStream($jaxl) {
            $_SESSION['jaxl_auth'] = 'connect';

            $xml = "";
            $xml .= "<body";
            $xml .= " content='".$jaxl->bosh['content']."'";
            $xml .= " hold='".$jaxl->bosh['hold']."'";
            $xml .= " xmlns='".$jaxl->bosh['xmlns']."'";
            $xml .= " wait='".$jaxl->bosh['wait']."'";
            $xml .= " rid='".++$jaxl->bosh['rid']."'";
            $xml .= " version='".$jaxl->bosh['version']."'";
            $xml .= " polling='".$jaxl->bosh['polling']."'";
            $xml .= " secure='".$jaxl->bosh['secure']."'";
            $xml .= " xmlns:xmpp='".$jaxl->bosh['xmlnsxmpp']."'";
            $xml .= " to='".$jaxl->domain."'";
            $xml .= " route='xmpp:".$jaxl->host.":".$jaxl->port."'";
            $xml .= " xmpp:version='".$jaxl->bosh['xmppversion']."'/>";
            
            $jaxl->sendXML($xml);
        }
        
        public static function endStream($jaxl) {
            $_SESSION['jaxl_auth'] = 'disconnect';

            $xml = "";
            $xml .= "<body";
            $xml .= " rid='".++$jaxl->bosh['rid']."'";
            $xml .= " sid='".$jaxl->bosh['sid']."'";
            $xml .= " type='terminate'";
            $xml .= " xmlns='".$jaxl->bosh['xmlns']."'>";
            $xml .= "<presence type='unavailable' xmlns='jabber:client'/>";
            $xml .= "</body>";
            
            $jaxl->sendXML($xml);
        }
        
        public static function restartStream($jaxl) {
            $xml = "";
            $xml .= "<body";
            $xml .= " rid='".++$jaxl->bosh['rid']."'";
            $xml .= " sid='".$jaxl->bosh['sid']."'";
            $xml .= " xmlns='".$jaxl->bosh['xmlns']."'";
            
            $xml .= " to='".$jaxl->host."'";
            $xml .= " xmpp:restart='true'";
            $xml .= " xmlns:xmpp='".$jaxl->bosh['xmlnsxmpp']."'/>";
            
            $_SESSION['jaxl_auth'] = false;
            $jaxl->sendXML($xml);
        }
        
        public static function ping($jaxl) {
            $xml = '';
            $xml .= '<body rid="'.++$jaxl->bosh['rid'].'"';
            $xml .= ' sid="'.$jaxl->bosh['sid'].'"';
            $xml .= ' xmlns="http://jabber.org/protocol/httpbind"/>';
            
            $_SESSION['jaxl_auth'] = true;
            $jaxl->sendXML($xml);
        }

        public static function out($jaxl, $payload) {
            JAXL0124::out($payload);
        }
        
    }
    
?>
