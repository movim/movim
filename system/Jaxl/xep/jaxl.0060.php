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
     * XEP-0060: Publisher-Subscriber
    */
    class JAXL0060 {

        public static $ns = 'http://jabber.org/protocol/pubsub';
        
        public static function init($jaxl) {
            $jaxl->features[] = self::$ns;

            JAXLXml::addTag('message', 'event', '//message/event/@xmlns');
            JAXLXml::addTag('message', 'retractId', '//message/items/retract/@id'); 
            
            JAXLXml::addTag('message', 'itemNode', '//message/items/@node');
            JAXLXml::addTag('message', 'itemId', '//message/items/item/@id');
            JAXLXml::addTag('message', 'itemPublisher', '//message/items/item/@publisher');
            
            JAXLXml::addTag('message', 'headers', '//message/headers/@xmlns');
            JAXLXml::addTag('message', 'header', '//message/headers/header');
            JAXLXml::addTag('message', 'headerName', '//message/headers/header/@name');
        }
        
        /*
         * Entity Use Cases
        */
        public static function discoFeatures($jaxl, $to, $from, $callback) {
            return JAXL0030::discoInfo($jaxl, $to, $from, $callback);
        }

        public static function discoNodes($jaxl, $to, $from, $callback, $node=false) {
            return JAXL0030::discoItems($jaxl, $to, $from, $callback, $node);
        }

        public static function discoNodeInfo($jaxl, $to, $from, $callback, $node=false) {
            return JAXL0030::discoInfo($jaxl, $to, $from, $callback, $node);
        }

        public static function discoNodeMeta($jaxl, $to, $from, $callback, $node=false) {
            return JAXL0030::discoInfo($jaxl, $to, $from, $callback, $node);
        }

        public static function discoNodeItems($jaxl, $to, $from, $callback, $node=false) {
            return JAXL0030::discoItems($jaxl, $to, $from, $callback, $node);
        }

        public static function getNodeSubscriptions($jaxl, $to, $from, $callback) {
            $payload = '';
            $payload .= '<pubsub xmlns="'.self::$ns.'">';
            $payload .= '<subscriptions/>';
            $payload .= '</pubsub>';
            return XMPPSend::iq($jaxl, 'get', $payload, $to, $from, $callback);
        }

        public static function getNodeAffiliations($jaxl, $to, $from, $callback) {
            $payload = '';
            $payload .= '<pubsub xmlns="'.self::$ns.'">';
            $payload .= '<affiliations/>';
            $payload .= '</pubsub>';
            return XMPPSend::iq($jaxl, 'get', $payload, $to, $from, $callback); 
        }

        /*
         * Subscriber Use Cases
        */
        public static function subscribe($jaxl, $to, $from, $node, $subJid=FALSE) {
            if(!$subJid) $subJid = $from;
            $payload = '';
            $payload .= '<pubsub xmlns="'.self::$ns.'">';
            $payload .= '<subscribe node="'.$node.'" jid="'.$subJid.'"/>';
            $payload .= '</pubsub>';
            return XMPPSend::iq($jaxl, 'set', $payload, $to, $from, $callback);
        }
        
        public static function unsubscribe($jaxl, $to, $from, $node, $subJid=FALSE) {
            if(!$subJid) $subJid = $from;
            $payload = '';
            $payload .= '<pubsub xmlns="'.self::$ns.'">';
            $payload .= '<unsubscribe node="'.$node.'" jid="'.$subJid.'"/>';
            $payload .= '</pubsub>';
            return XMPPSend::iq($jaxl, 'set', $payload, $to, $from, $callback); 
        }
        
        public static function getSubscriptionOption($jaxl, $to, $from, $node, $subJid=FALSE) {
            if(!$subJid) $subJid = $from;
            $payload = '';
            $payload .= '<pubsub xmlns="'.self::$ns.'">';
            $payload .= '<options node="'.$node.'" jid="'.$subJid.'"/>';
            $payload .= '</pubsub>';
            return XMPPSend::iq($jaxl, 'get', $payload, $to, $from, $callback);
        }
        
        public static function setSubscriptionOption($jaxl, $to, $from, $node, $subJid=FALSE) {
            
        }
        
        public static function getNodeItems($jaxl, $to, $from, $node) {
            $payload = '';
            $payload .= '<pubsub xmlns="'.self::$ns.'">';
            $payload .= '<item node="'.$node.'"/>';
            $payload .= '</pubsub>';
            return XMPPSend::iq($jaxl, 'get', $payload, $to, $from, $callback);
        }
        
        /*
         * Publisher Use Cases
        */
        public static function publishItem($jaxl, $to, $from, $node, $item, $itemId=FALSE, $callback) {
            $payload = '';
            $payload .= '<pubsub xmlns="'.self::$ns.'">';
            $payload .= '<publish node="'.$node.'">';
            if($itemId) $payload .= '<item id="'.$itemId.'">';
            else $payload .= '<item>';
            $payload .= $item;
            $payload .= '</item>';
            $payload .= '</publish>';
            $payload .= '</pubsub>';
            return XMPPSend::iq($jaxl, 'set', $payload, $to, $from, $callback);
        }
        
        public static function deleteItem($jaxl, $to, $from, $node, $itemId, $callback) {
            $payload = '';
            $payload .= '<pubsub xmlns="'.self::$ns.'">';
            $payload .= '<retract node="'.$node.'">';
            $payload .= '<item id="'.$itemId.'"/>';
            $payload .= '</retract>';
            $payload .= '</pubsub>';
            return XMPPSend::iq($jaxl, 'set', $payload, $to, $from, $callback);
        }
        
        /*
         * Owner Use Cases
        */
        public static function createNode($jaxl, $to, $from, $node, $callback) {
            $payload = '';
            $payload .= '<pubsub xmlns="'.self::$ns.'">';
            $payload .= '<create node="'.$node.'"/>';
            $payload .= '</pubsub>';
            return XMPPSend::iq($jaxl, 'set', $payload, $to, $from, $callback);
        }
        
        public static function configureNode($jaxl, $to, $from, $node, $callback) {
            $payload = '';
            $payload .= '<pubsub xmlns="'.self::$ns.'">';
            $payload .= '<configure node="'.$node.'"/>';
            $payload .= '</pubsub>';
            return XMPPSend::iq($jaxl, 'set', $payload, $to, $from, $callback); 
        }
        
        public static function getDefaultNodeConfig($jaxl, $to, $from, $callback) {
            $payload = '';
            $payload .= '<pubsub xmlns="'.self::$ns.'#owner">';
            $payload .= '<default/>';
            $payload .= '</pubsub>';
            return XMPPSend::iq($jaxl, 'set', $payload, $to, $from, $callback); 
        }
        
        public static function deleteNode($jaxl, $to, $from, $node, $redirectURI=FALSE, $callback) {
            $payload = '';
            $payload .= '<pubsub xmlns="'.self::$ns.'#owner">';
            $payload .= '<delete node="'.$node.'">';
            if($redirectURI) $payload .= '<redirect uri="'.$redirectURI.'"/>';
            $payload .= '</delete>';
            $payload .= '</pubsub>';
            return XMPPSend::iq($jaxl, 'set', $payload, $to, $from, $callback); 
        }
        
        public static function purgeNode($jaxl, $to, $from, $node, $callback) {
            $payload = '';
            $payload .= '<pubsub xmlns="'.self::$ns.'#owner">';
            $payload .= '<purge node="'.$node.'"/>';
            $payload .= '</pubsub>';
            return XMPPSend::iq($jaxl, 'set', $payload, $to, $from, $callback); 
        }
        
        public static function getSubscriberList($jaxl, $to, $from, $node, $callback) {
            $payload = '';
            $payload .= '<pubsub xmlns="'.self::$ns.'#owner">';
            $payload .= '<subscriptions node="'.$node.'"/>';
            $payload .= '</pubsub>';
            return XMPPSend::iq($jaxl, 'get', $payload, $to, $from, $callback); 
        }
        
        public static function updateSubscription($jaxl, $to, $from, $node, $jid, $subscription) {
            if(!is_array($jid) && !is_array($subscription)) {
                $jid[] = $jid;
                $subscription[] = $subscription;
            }
            
            $payload = '';
            $payload .= '<pubsub xmlns="'.self::$ns.'">';
            $payload .= '<subscriptions node="'.$node.'">';
            foreach($jid as $k=>$v) $payload .= '<subscription jid="'.$jid[$k].'" subscription="'.$subscription[$k].'"/>';
            $payload .= '</subscriptions>';
            $payload .= '</pubsub>';
            return XMPPSend::iq($jaxl, 'set', $payload, $to, $from, $callback); 
        }
        
        public static function getAffiliationList($jaxl, $to, $from, $node, $callback) {
            $payload = '';
            $payload .= '<pubsub xmlns="'.self::$ns.'#owner">';
            $payload .= '<affiliations node="'.$node.'"/>';
            $payload .= '</pubsub>';
            return XMPPSend::iq($jaxl, 'get', $payload, $to, $from, $callback);  
        }
        
        public static function updateAffiliation($jaxl, $to, $from, $node, $jid, $affiliation) {
            if(!is_array($jid) && !is_array($affiliation)) {
                $jid[] = $jid;
                $affiliation[] = $affiliation;
            }
            
            $payload = '';
            $payload .= '<pubsub xmlns="'.self::$ns.'">';
            $payload .= '<affiliations node="'.$node.'">';
            foreach($jid as $k=>$v) $payload .= '<affiliation jid="'.$jid[$k].'" affiliation="'.$affiliation[$k].'"/>';
            $payload .= '</affiliations>';
            $payload .= '</pubsub>';
            return XMPPSend::iq($jaxl, 'set', $payload, $to, $from, $callback);
        }
        
    }

?>
