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
     * XEP-0045: Mutli-User Chat Implementation
    */
    class JAXL0045 {
        
        public static $ns = 'http://jabber.org/protocol/muc';   
    
        public static function init($jaxl) {
            $jaxl->features[] = self::$ns;
            
            JAXLXml::addTag('presence', 'itemJid', '//presence/x/item/@jid');
            JAXLXml::addTag('presence', 'itemAffiliation', '//presence/x/item/@affiliation');
            JAXLXml::addTag('presence', 'itemRole', '//presence/x/item/@role');
        }
        
        /*
         * Occupant Use Cases
        */
        public static function joinRoom($jaxl, $jid, $roomJid, $history=0, $type='seconds') {
            $child = array();
            $child['payload'] = '';
            $child['payload'] .= '<x xmlns="'.self::$ns.'">';
            $child['payload'] .= '<history '.$type.'="'.$history.'"/>';
            $child['payload'] .= '</x>';
            return XMPPSend::presence($jaxl, $roomJid, $jid, $child, false);
        }
        
        public static function exitRoom($jaxl, $jid, $roomJid) {
            return XMPPSend::presence($jaxl, $roomJid, $jid, false, "unavailable");
        }

        /*
         * Moderator Use Cases
        */
        public static function kickOccupant($jaxl, $fromJid, $nick, $roomJid, $reason=false, $callback=false) {
            $payload = '<query xmlns="'.self::$ns.'#admin">';
            $payload .= '<item role="none" nick="'.$nick.'">';
            if($reason) $payload .= '<reason>'.$reason.'</reason>';
            $payload .= '</item>';
            $payload .= '</query>';
            return XMPPSend::iq($jaxl, "set", $payload, $roomJid, $fromJid, $callback);
        }

        /*
         * Admin Use Cases
        */
        public static function banUser() {

        }

        public static function grantModeratorPrivileges() {
            
        }

        public static function revokeModeratorPrivileges() {

        }

        public static function modifyModeratorList() {
            
        }
        
        /*
         * Owner Use Cases
        */
        public static function createRoom() {

        }

        public static function getUniqueRoomName() {

        }
        
        public static function configureRoom() {

        }

        public static function getRoomConfig($jaxl, $jid, $roomJid, $callback=false) {
            $payload = '<query xmlns="'.self::$ns.'#owner"/>';
            return XMPPSend::iq($jaxl, "get", $payload, $roomJid, $jid, $callback);
        }
        
        public static function setRoomConfig($jaxl, $jid, $roomJid, $fields, $callback=false) {
            $payload = JAXL0004::setFormField($fields, false, false, 'submit');
            $payload = '<query xmlns="'.self::$ns.'#owner">'.$payload.'</query>';
            return XMPPSend::iq($jaxl, "set", $payload, $roomJid, $jid, $callback);
        }

        public static function grantOwnerPrivileges($jaxl, $fromJid, $toJid, $roomJid, $reason=false, $callback=false) {
            $payload = '<query xmlns="'.self::$ns.'#admin">';
            $payload .= '<item affiliation="owner" jid="'.$toJid.'">';
            if($reason) $payload .= '<reason>'.$reason.'</reason>';
            $payload .= '</item>';
            $payload .= '</query>';
            return XMPPSend::iq($jaxl, "set", $payload, $roomJid, $fromJid, $callback);
        }
        
        public static function revokeOwnerPrivileges($jaxl, $fromJid, $toJid, $roomJid, $reason=false, $callback=false) {
            $payload = '<query xmlns="'.self::$ns.'#admin">';
            $payload .= '<item affiliation="member" jid="'.$toJid.'">';
            if($reason) $payload .= '<reason>'.$reason.'</reason>';
            $payload .= '</item>';
            $payload .= '</query>';
            return XMPPSend::iq($jaxl, "set", $payload, $roomJid, $fromJid, $callback);
        }
        
        public static function modifyOwnerList() {
            
        }
        
        public static function grantAdminPrivileges($jaxl, $fromJid, $toJid, $roomJid, $reason=false, $callback=false) {
            $payload = '<query xmlns="'.self::$ns.'#admin">';
            $payload .= '<item affiliation="admin" jid="'.$toJid.'">';
            if($reason) $payload .= '<reason>'.$reason.'</reason>';
            $payload .= '</item>';
            $payload .= '</query>';
            return XMPPSend::iq($jaxl, "set", $payload, $roomJid, $fromJid, $callback);
        }

        public static function removeAdminPrivileges($jaxl, $fromJid, $toJid, $roomJid, $reason=false, $callback=false) {
            $payload = '<query xmlns="'.self::$ns.'#admin">';
            $payload .= '<item affiliation="member" jid="'.$toJid.'">';
            if($reason) $payload .= '<reason>'.$reason.'</reason>';
            $payload .= '</item>';
            $payload .= '</query>';
            return XMPPSend::iq($jaxl, "set", $payload, $roomJid, $fromJid, $callback);
        }

        public static function modifyAdminList() {

        }

        public static function destroyRoom() {

        }
        
    }
    
?>
