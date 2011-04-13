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
     * XEP-0202 : Entity Time
    */  
    class JAXL0202 {
        
        public static $ns = 'urn:xmpp:time';
        
        public static function init($jaxl) {
            $jaxl->features[] = self::$ns;
            
            JAXLXml::addTag('iq', 'time', '//iq/time/@xmlns');
            JAXLXml::addTag('iq', 'timeTZO', '//iq/time/tzo');
            JAXLXml::addTag('iq', 'timeUTC', '//iq/time/utc');

            $jaxl->addPlugin('jaxl_get_iq_get', array('JAXL0202', 'handleIq'));
        }
        
        public static function getEntityTime($jaxl, $to, $from, $callback) {
            $payload = '<time xmlns="'.self::$ns.'"/>';
            return XMPPSend::iq($jaxl, 'get', $payload, $to, $from, $callback);
        }
        
        public static function handleIq($payload, $jaxl) {
            if(@$payload['time'] == self::$ns) {
                $entityTime = '<time xmlns="'.self::$ns.'">';
                $entityTime .= '<tzo>'.date('P').'</tzo>';
                $entityTime .= '<utc>'.date('Y-m-d').'T'.date('H:i:s').'Z</utc>';
                $entityTime .= '</time>';
                return XMPPSend::iq($jaxl, 'result', $entityTime, $payload['from'], $payload['to'], false, $payload['id']);
            }
            return $payload;
        }
        
    }
    
?>
