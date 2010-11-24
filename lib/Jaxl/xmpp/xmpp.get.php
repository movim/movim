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
 * @subpackage xmpp
 * @author Abhinav Singh <me@abhinavsingh.com>
 * @copyright Abhinav Singh
 * @link http://code.google.com/p/jaxl 
 */
    
    // include required classes
    jaxl_require(array(
        'JAXLPlugin',
        'JAXLog',
        'JAXLXml',
        'XMPPAuth',
        'XMPPSend'
    ));
    
    /**
     * XMPP Get Class
     * Provide methods for receiving all kind of xmpp streams and stanza's
    */
    class XMPPGet {
        
        public static function streamStream($arr, $jaxl) {
            if($arr['@']["xmlns:stream"] != "http://etherx.jabber.org/streams") {
                print "Unrecognized XMPP Stream...\n";
            }
            else if($arr['@']['xmlns'] == "jabber:component:accept") {
                JAXLPlugin::execute('jaxl_post_start', $arr['@']['id'], $jaxl);
            }
            else if($arr['@']['xmlns'] == "jabber:client") {
                $jaxl->streamId = $arr['@']['id'];
                $jaxl->streamHost = $arr['@']['from'];
                $jaxl->streamVersion = $arr['@']['version'];
            }
        }
        
        public static function streamFeatures($arr, $jaxl) {
            if(isset($arr["#"]["starttls"]) && ($arr["#"]["starttls"][0]["@"]["xmlns"] == "urn:ietf:params:xml:ns:xmpp-tls")) {
                XMPPSend::startTLS($jaxl);
            }
            else if(isset($arr["#"]["mechanisms"]) && ($arr["#"]["mechanisms"][0]["@"]["xmlns"] == "urn:ietf:params:xml:ns:xmpp-sasl")) {
                $mechanism = array();
                
                foreach ($arr["#"]["mechanisms"][0]["#"]["mechanism"] as $row)
                    $mechanism[] = $row["#"];
                
                JAXLPlugin::execute('jaxl_get_auth_mech', $mechanism, $jaxl);
            }
            else if(isset($arr["#"]["bind"]) && ($arr["#"]["bind"][0]["@"]["xmlns"] == "urn:ietf:params:xml:ns:xmpp-bind")) {
                $jaxl->sessionRequired = isset($arr["#"]["session"]);
                $jaxl->startBind();
            }
        }
        
        public static function streamError($arr, $jaxl) {
            $desc = key($arr['#']);
            $xmlns = $arr['#'][$desc]['0']['@']['xmlns'];
            JAXLPlugin::execute('jaxl_get_stream_error', $arr, $jaxl);
            $jaxl->log("Stream error with description ".$desc." and xmlns ".$xmlns, 1);
            return true;
        }

        public static function failure($arr, $jaxl) {
            $xmlns = $arr['xmlns'];
            switch($xmlns) {
                case 'urn:ietf:params:xml:ns:xmpp-tls':
                    $jaxl->log("Unable to start TLS negotiation, see logs for detail...", 1);
                    JAXLPlugin::execute('jaxl_post_auth_failure', false, $jaxl);
                    $jaxl->shutdown('tlsFailure');
                    break;
                case 'urn:ietf:params:xml:ns:xmpp-sasl':
                    $jaxl->log("Unable to complete SASL Auth, see logs for detail...", 1);
                    JAXLPlugin::execute('jaxl_post_auth_failure', false, $jaxl);
                    $jaxl->shutdown('saslFailure');
                    break;
                default:
                    $jaxl->log("Uncatched failure xmlns received...", 1);
                    break;
            }
        }
        
        public static function proceed($arr, $jaxl) {
            if($arr['xmlns'] == "urn:ietf:params:xml:ns:xmpp-tls") {
                stream_set_blocking($jaxl->stream, 1);
                if(!stream_socket_enable_crypto($jaxl->stream, true, STREAM_CRYPTO_METHOD_TLS_CLIENT))
                    stream_socket_enable_crypto($jaxl->stream, true, STREAM_CRYPTO_METHOD_SSLv3_CLIENT);
                stream_set_blocking($jaxl->stream, 0);
                XMPPSend::startStream($jaxl);
            }
        }
        
        public static function challenge($arr, $jaxl) {
            if($arr['xmlns'] == "urn:ietf:params:xml:ns:xmpp-sasl") {
                if($jaxl->secondChallenge) $xml = '<response xmlns="urn:ietf:params:xml:ns:xmpp-sasl"/>';
                else $xml = XMPPAuth::getResponse($jaxl->authType, $arr['challenge'], $jaxl);
                $jaxl->sendXML($xml);
            }
        }
        
        public static function success($arr, $jaxl) {
            if($arr['xmlns'] == "urn:ietf:params:xml:ns:xmpp-sasl") {
                if($jaxl->mode == "cgi") JAXL0206::restartStream($jaxl);
                else XMPPSend::startStream($jaxl);
            }
        }
        
        public static function presence($arrs, $jaxl) {
            $payload = array();
            foreach($arrs as $arr) $payload[] = $arr;
            JAXLPlugin::execute('jaxl_get_presence', $payload, $jaxl);
            unset($payload);
            return $arrs;
        }
        
        public static function message($arrs, $jaxl) {
            $payload = array();
            foreach($arrs as $arr) $payload[] = $arr;
            JAXLPlugin::execute('jaxl_get_message', $payload, $jaxl);
            unset($payload);
            return $arrs;
        }
        
        public static function postBind($arr, $jaxl) {
            if($arr["type"] == "result") {
                $jaxl->jid = $arr["bindJid"];
                
                JAXLPlugin::execute('jaxl_post_bind', false, $jaxl);
                
                if($jaxl->sessionRequired) {
                    $jaxl->startSession();
                }
                else {
                    $jaxl->auth = true;
                    $jaxl->log("Auth completed...", 1);
                    JAXLPlugin::execute('jaxl_post_auth', false, $jaxl);
                }
            }
        }
        
        public static function postSession($arr, $jaxl) {
            if($arr["type"] == "result") {
                $jaxl->auth = true;
                $jaxl->log("Auth completed...", 1);
                JAXLPlugin::execute('jaxl_post_auth', false, $jaxl);
            }
        }
        
        public static function iq($arr, $jaxl) {
            switch($arr['type']) {
                case 'result':
                    $id = $arr['id'];
                    JAXLPlugin::execute('jaxl_get_iq_'.$id, $arr, $jaxl);
                    break;
                case 'get':
                    JAXLPlugin::execute('jaxl_get_iq_get', $arr, $jaxl);
                    break;
                case 'set':
                    JAXLPlugin::execute('jaxl_get_iq_set', $arr, $jaxl);
                    break;
                case 'error':
                    JAXLPlugin::execute('jaxl_get_iq_error', $arr, $jaxl);
                    break;
                default:
                    $jaxl->log('Unhandled iq type ...'.json_encode($arr), 1);
                    break;
            }
            return $arr;
        }
        
    }
    
?>
