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
     * XEP-0092: Software Version
    */
    class JAXL0092 {
        
        public static $ns = 'jabber:iq:version';

        public static function init($jaxl) {
            $jaxl->features[] = self::$ns;
            
            JAXLXml::addTag('iq', 'softwareName', '//iq/query[@xmlns="'.self::$ns.'"]/name');
            JAXLXml::addTag('iq', 'softwareVersion', '//iq/query[@xmlns="'.self::$ns.'"]/version');
            JAXLXml::addTag('iq', 'softwareOS', '//iq/query[@xmlns="'.self::$ns.'"]/os');
            
            $jaxl->addPlugin('jaxl_get_iq_get', array('JAXL0092', 'getIq'));
        }
        
        public static function getIq($arr, $jaxl) {
            if($arr['queryXmlns'] == self::$ns) {
                $payload = '<query xmlns="'.self::$ns.'">';
                $payload .= '<name>'.$jaxl->getName().'</name>';
                $payload .= '<version>'.$jaxl->getVersion().'</version>';
                $payload .= '<os>'.PHP_OS.'</os>';
                $payload .= '</query>';
                
                return XMPPSend::iq($jaxl, 'result', $payload, $arr['from'], $arr['to'], false, $arr['id']);
            }
            return $arr;
        }
        
        public static function getVersion($jaxl, $fromJid, $toJid, $callback) {
            $payload = '<query xmlns="'.self::$ns.'">';
            return XMPPSend::iq($jaxl, 'get', $payload, $fromJid, $toJid, $callback);
        }
        
    }

?>
