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
     * XEP-0115 : Entity Capabilities
    */
    class JAXL0115 {
        
        public static $ns = 'http://jabber.org/protocol/caps';

        public static function init($jaxl) {
            $jaxl->features[] = self::$ns;

            JAXLXml::addTag('presence', 'cXmlns', '//presence/c/@xmlns');
            JAXLXml::addTag('presence', 'cHash', '//presence/c/@hash');
            JAXLXml::addTag('presence', 'cNode', '//presence/c/@node');
            JAXLXml::addTag('presence', 'cVer', '//presence/c/@ver');
            
            JAXLXml::addTag('iq', 'queryNode', '//iq/query/@node');
        }
        
        public static function getCaps($jaxl, $features=false) {
            $payload = '<c';
            $payload .= ' xmlns="'.self::$ns.'"';
            $payload .= ' hash="sha1"';
            $payload .= ' node="http://code.google.com/p/jaxl"';
            $payload .= ' ver="'.self::getVerificationString($jaxl, $features).'"';
            $payload .= '/>';
            return $payload;
        }
        
        public static function getVerificationString($jaxl, $features) {
            asort($features);
            $S = $jaxl->category.'/'.$jaxl->type.'/'.$jaxl->lang.'/'.$jaxl->getName().'<';
            foreach($features as $feature) $S .= $feature.'<';
            $ver = base64_encode(sha1($S, true));
            return $ver;
        }
        
    }
    
?>
