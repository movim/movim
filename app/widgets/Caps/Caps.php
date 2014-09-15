<?php

/**
 * @package Widgets
 *
 * @file AdminDB.php
 * This file is part of Movim.
 *
 * @brief Capabilities support array
 *
 * @author Jaussoin Timothée <edhelas@movim.eu>

 * Copyright (C)2014 Movim project
 *
 * See COPYING for licensing information.
 */
 
class Caps extends WidgetBase
{
    private $_table = array();
    private $_nslist;
    
    function load() {
        $this->addcss('caps.css');
        
        $cd = new \modl\CapsDAO();
        $clients = $cd->getClients();

        foreach($clients as $c) {
            if(!isset($this->_table[$c->name])) {
                $this->_table[$c->name] = array();
            }
            
            $features = unserialize($c->features);
            foreach($features as $f) {
                if(!in_array($f, $this->_table[$c->name])) {
                    array_push($this->_table[$c->name], (string)$f);
                }
            }
        }

        ksort($this->_table);

        $this->_nslist = array(
            '0012' => array('name' => 'Last Activity',          'category' => 'chat',       'ns' => 'jabber:iq:last'),
            '0050' => array('name' => 'Ad-Hoc Commands',        'category' => 'client',     'ns' => 'http://jabber.org/protocol/commands'),
            '0080' => array('name' => 'User Location',          'category' => 'profile',    'ns' => 'http://jabber.org/protocol/geoloc'),
            '0071' => array('name' => 'XHTML-IM',               'category' => 'chat',       'ns' => 'http://jabber.org/protocol/xhtml-im'),
            '0084' => array('name' => 'User Avatar',            'category' => 'profile',    'ns' => 'urn:xmpp:avatar:data'),
            '0085' => array('name' => 'Chat State Notifications', 'category' => 'chat',      'ns' => 'http://jabber.org/protocol/chatstates'),
            '0092' => array('name' => 'Software Version',       'category' => 'client',     'ns' => 'jabber:iq:version'),
            '0108' => array('name' => 'User Activity',          'category' => 'profile',    'ns' => 'http://jabber.org/protocol/activity'),
            '0115' => array('name' => 'Entity Capabilities',    'category' => 'client',     'ns' => 'http://jabber.org/protocol/caps'),
            '0118' => array('name' => 'User Tune',              'category' => 'profile',    'ns' => 'http://jabber.org/protocol/tune'),
            '0124' => array('name' => 'Bidirectional-streams Over Synchronous HTTP (BOSH)', 'category' => 'client',    'ns' => 'http://jabber.org/protocol/httpbind'),
            '0152' => array('name' => 'Reachability Addresses', 'category' => 'client',     'ns' => 'urn:xmpp:reach:0'),
            '0166' => array('name' => 'Jingle',                 'category' => 'jingle',     'ns' => 'urn:xmpp:jingle:1'),
            '0167' => array('name' => 'Jingle RTP Sessions',    'category' => 'jingle',     'ns' => 'urn:xmpp:jingle:apps:rtp:1'),
            '0176' => array('name' => 'Jingle ICE-UDP Transport Method', 'category' => 'jingle',     'ns' => 'urn:xmpp:jingle:transports:ice-udp:1'),
            '0177' => array('name' => 'Jingle Raw UDP Transport Method', 'category' => 'jingle',     'ns' => 'urn:xmpp:jingle:transports:raw-udp:1'),
            '0184' => array('name' => 'Message Delivery Receipts', 'category' => 'chat',       'ns' => 'urn:xmpp:receipts'),
            '0186' => array('name' => 'Invisible Command',      'category' => 'chat',       'ns' => 'urn:xmpp:invisible:0'),
            '0199' => array('name' => 'XMPP Ping',              'category' => 'client',     'ns' => 'urn:xmpp:ping'),
            '0202' => array('name' => 'Entity Time',            'category' => 'client',     'ns' => 'urn:xmpp:time'),
            '0224' => array('name' => 'Attention',              'category' => 'chat',       'ns' => 'urn:xmpp:attention:0'),
            '0231' => array('name' => 'Bits of Binary',         'category' => 'chat',       'ns' => 'urn:xmpp:bob'),
            '0234' => array('name' => 'Jingle File Transfer',   'category' => 'jingle',     'ns' => 'urn:xmpp:jingle:apps:file-transfer:4'),
            '0249' => array('name' => 'Direct MUC Invitations', 'category' => 'chat',       'ns' => 'jabber:x:conference'),
            '0277' => array('name' => 'Microblogging over XMPP', 'category' => 'social',     'ns' => 'urn:xmpp:microblog:0'),
            '0292' => array('name' => 'vCard4 Over XMPP',       'category' => 'profile',    'ns' => 'urn:xmpp:vcard4'),
            '0301' => array('name' => 'In-Band Real Time Text', 'category' => 'chat',       'ns' => 'urn:xmpp:rtt:0'),
            '0308' => array('name' => 'Last Message Correction', 'category' => 'chat',       'ns' => 'urn:xmpp:message-correct:0'),
            '0313' => array('name' => 'Message Archive Management', 'category' => 'chat',       'ns' => 'urn:xmpp:mam:0'),
            '0320' => array('name' => 'Use of DTLS-SRTP in Jingle Sessions', 'category' => 'jingle',     'ns' => 'urn:xmpp:jingle:apps:dtls:0'),
            '0323' => array('name' => 'Internet of Things - Sensor Data', 'category' => 'iot',        'ns' => 'urn:xmpp:iot:sensordata'),
            '0324' => array('name' => 'Internet of Things - Provisioning', 'category' => 'iot',        'ns' => 'urn:xmpp:iot:provisioning'),
            '0325' => array('name' => 'Internet of Things - Control', 'category' => 'iot',        'ns' => 'urn:xmpp:iot:control'),
            '0326' => array('name' => 'Internet of Things - Concentrators', 'category' => 'iot',        'ns' => 'urn:xmpp:iot:concentrators'),
            '0327' => array('name' => 'Rayo', 'category' => 'rayo',       'ns' => 'urn:xmpp:rayo:0'),
            '0330' => array('name' => 'Pubsub Subscription',    'category' => 'social',     'ns' => 'urn:xmpp:pubsub:subscription'),
            '0332' => array('name' => 'HTTP over XMPP transport', 'category' => 'client',     'ns' => 'urn:xmpp:http'),
            '0337' => array('name' => 'Event Logging over XMPP', 'category' => 'client',     'ns' => 'urn:xmpp:eventlog'),
            '0338' => array('name' => 'Jingle Grouping Framework', 'category' => 'jingle',     'ns' => 'urn:ietf:rfc:5888'),
            '0339' => array('name' => 'Source-Specific Media Attributes in Jingle', 'category' => 'jingle',     'ns' => 'urn:ietf:rfc:5576'),
            '0340' => array('name' => 'COnferences with LIghtweight BRIdging (COLIBRI)', 'category' => 'jingle',     'ns' => 'http://jitsi.org/protocol/colibri'),
            '0341' => array('name' => 'Rayo CPA', 'category' => 'rayo',       'ns' => 'urn:xmpp:rayo:cpa:0'),
            '0342' => array('name' => 'Rayo Fax', 'category' => 'rayo',       'ns' => 'urn:xmpp:rayo:fax:1'),
            '0347' => array('name' => 'Internet of Things - Discovery', 'category' => 'iot',        'ns' => 'urn:xmpp:iot:discovery'),
            '0348' => array('name' => 'Signing Forms', 'category' => 'client',     'ns' => 'urn:xmpp:xdata:signature:oauth1'),
            );
    }

    function isImplemented($client, $key) {
        if(in_array($this->_nslist[$key]['ns'], $client)) {
            return '
                <td
                    class="yes '.$this->_nslist[$key]['category'].'"
                    title="XEP-'.$key.': '.$this->_nslist[$key]['name'].'">'.
                    $key.'
                </td>';
        } else {
            return '
                <td
                    class="no  '.$this->_nslist[$key]['category'].'"
                    title="XEP-'.$key.': '.$this->_nslist[$key]['name'].'">'.
                    $key.'
                </td>';
        }
    }

    function display()
    {
        $this->view->assign('table', $this->_table);
        $this->view->assign('nslist', $this->_nslist);
    }
}
