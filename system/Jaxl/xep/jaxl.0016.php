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
     * XEP-0016 : Privacy Lists
    */
    class JAXL0016 {

        public static $ns = 'jabber:iq:privacy';

        public static function init($jaxl) {
            $jaxl->features[] = self::$ns;

            JAXLXml::addTag('iq', 'activeList', '//iq/query/active/@name');
            JAXLXml::addTag('iq', 'defaultList', '//iq/query/default/@name');
            JAXLXml::addTag('iq', 'listName', '//iq/query/list/@name');
            JAXLXml::addTag('iq', 'listItemType', '//iq/query/list/item/@type');
            JAXLXml::addTag('iq', 'listItemValue', '//iq/query/list/item/@value');
            JAXLXml::addTag('iq', 'listItemAction', '//iq/query/list/item/@action');
            JAXLXml::addTag('iq', 'listItemOrder', '//iq/query/list/item/@order');
            JAXLXml::addTag('iq', 'listItemChild', '//iq/query/list/item/*/name()');
        }

        public static function getListNames($jaxl, $callback) {
            $payload = '<query xmlns="'.self::$ns.'"/>';
            return XMPPSend::iq($jaxl, 'get', false, false, $callback);
        }

        public static function getList($jaxl, $list, $callback) {
            $payload = '<query xmlns="'.self::$ns.'">';
            $payload .= '<list name="'.$list.'"/>';
            $payload .= '</query>';
            return XMPPSend::iq($jaxl, 'get', false, false, $callback);
        }

        public static function setActiveList($jaxl, $list, $callback) {
            $payload = '<query xmlns="'.self::$ns.'">';
            if($list) $payload .= '<active name="'.$list.'"/>';
            else $payload .= '<active/>';
            $payload .= '</query>';
            return XMPPSend::iq($jaxl, 'set', false, false, $callback);
        }

        public static function setDefaultList($jaxl, $list, $callback) {
            $payload = '<query xmlns="'.self::$ns.'">';
            if($list) $payload .= '<default name="'.$list.'"/>';
            else $payload .= '<default/>';
            $payload .= '</query>';
            return XMPPSend::iq($jaxl, 'set', false, false, $callback);
        }

        public static function editList() {

        }

        public static function ackListPush() {

        }

        public static function removeList() {

        }

    }

?>
