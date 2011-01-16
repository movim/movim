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
     * XEP-0114: Jabber Component Protocol
    */
    class JAXL0114 {
        
        public static function init($jaxl) {
            // initialize working parameter for this jaxl instance
            $jaxl->comp = array(
                'host'  =>  false,
                'pass'  =>  false
            );

            // parse user options
            $jaxl->comp['host'] = $jaxl->getConfigByPriority(@$jaxl->config['compHost'], "JAXL_COMPONENT_HOST", $jaxl->comp['host']);
            $jaxl->comp['pass'] = $jaxl->getConfigByPriority(@$jaxl->config['compPass'], "JAXL_COMPONENT_PASS", $jaxl->comp['pass']);
           
            // register required callbacks
            $jaxl->addPlugin('jaxl_post_start', array('JAXL0114', 'handshake'));
            $jaxl->addPlugin('jaxl_pre_handler', array('JAXL0114', 'preHandler'));
        }
        
        public static function startStream($jaxl, $payload) {
            $xml = '<stream:stream xmlns="jabber:component:accept" xmlns:stream="http://etherx.jabber.org/streams" to="'.$jaxl->comp['host'].'">';
            $jaxl->sendXML($xml);
        }
        
        public static function handshake($id, $jaxl) {
            $hash = strtolower(sha1($id.$jaxl->comp['pass']));
            $xml = '<handshake>'.$hash.'</handshake>';
            $jaxl->sendXML($xml);
        }

        public static function preHandler($xml, $jaxl) {
            if($xml == '<handshake/>') {
                $xml = '';
                $jaxl->executePlugin('jaxl_post_handshake', false);
            }
            return $xml;
        }
        
    }

?>
