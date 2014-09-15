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
    private $_table;
    private $_nslist;
    
    function load() {
        $this->addcss('caps.css');
        
        $cd = new \modl\CapsDAO();
        $clients = $cd->getClients();

        $this->_table = array();

        foreach($clients as $c) {
            if(isset($table[$c->name])) {
                $this->_table[$c->name] = array_merge($this->_table[$c->name], unserialize($c->features));
            } else {
                $this->_table[$c->name] = unserialize($c->features);
            }
        }

        ksort($this->_table);

        $this->_nslist = array(
            '0012' => array('category' => 'chat',       'ns' => 'jabber:iq:last'),
            '0050' => array('category' => 'client',     'ns' => 'http://jabber.org/protocol/commands'),
            '0071' => array('category' => 'chat',       'ns' => 'http://jabber.org/protocol/xhtml-im'),
            '0084' => array('category' => 'profile',    'ns' => 'urn:xmpp:avatar:data'),
            '0085' => array('category' => 'chat',       'ns' => 'http://jabber.org/protocol/chatstates'),
            '0092' => array('category' => 'client',     'ns' => 'jabber:iq:version'),
            '0115' => array('category' => 'client',     'ns' => 'http://jabber.org/protocol/caps'),
            '0152' => array('category' => 'client',     'ns' => 'urn:xmpp:reach:0'),
            '0166' => array('category' => 'jingle',     'ns' => 'urn:xmpp:jingle:1'),
            '0167' => array('category' => 'jingle',     'ns' => 'urn:xmpp:jingle:apps:rtp:1'),
            '0176' => array('category' => 'jingle',     'ns' => 'urn:xmpp:jingle:transports:ice-udp:1'),
            '0177' => array('category' => 'jingle',     'ns' => 'urn:xmpp:jingle:transports:raw-udp:1'),
            '0184' => array('category' => 'chat',       'ns' => 'urn:xmpp:receipts'),
            '0186' => array('category' => 'chat',       'ns' => 'urn:xmpp:invisible:0'),
            '0199' => array('category' => 'client',     'ns' => 'urn:xmpp:ping'),
            '0202' => array('category' => 'client',     'ns' => 'urn:xmpp:time'),
            '0224' => array('category' => 'chat',       'ns' => 'urn:xmpp:attention:0'),
            '0231' => array('category' => 'chat',       'ns' => 'urn:xmpp:bob'),
            '0234' => array('category' => 'jingle',     'ns' => 'urn:xmpp:jingle:apps:file-transfer:4'),
            '0249' => array('category' => 'chat',       'ns' => 'jabber:x:conference'),
            '0277' => array('category' => 'social',     'ns' => 'urn:xmpp:microblog:0'),
            '0292' => array('category' => 'profile',    'ns' => 'urn:xmpp:vcard4'),
            '0301' => array('category' => 'chat',       'ns' => 'urn:xmpp:rtt:0'),
            '0308' => array('category' => 'chat',       'ns' => 'urn:xmpp:message-correct:0'),
            '0313' => array('category' => 'chat',       'ns' => 'urn:xmpp:mam:0'),
            '0320' => array('category' => 'jingle',     'ns' => 'urn:xmpp:jingle:apps:dtls:0'),
            '0323' => array('category' => 'iot',        'ns' => 'urn:xmpp:iot:sensordata'),
            '0324' => array('category' => 'iot',        'ns' => 'urn:xmpp:iot:provisioning'),
            '0325' => array('category' => 'iot',        'ns' => 'urn:xmpp:iot:control'),
            '0326' => array('category' => 'iot',        'ns' => 'urn:xmpp:iot:concentrators'),
            '0327' => array('category' => 'rayo',       'ns' => 'urn:xmpp:rayo:0'),
            '0332' => array('category' => 'client',     'ns' => 'urn:xmpp:http'),
            '0337' => array('category' => 'client',     'ns' => 'urn:xmpp:eventlog'),
            '0338' => array('category' => 'jingle',     'ns' => 'urn:ietf:rfc:5888'),
            '0339' => array('category' => 'jingle',     'ns' => 'urn:ietf:rfc:5576'),
            '0340' => array('category' => 'jingle',     'ns' => 'http://jitsi.org/protocol/colibri'),
            '0341' => array('category' => 'rayo',       'ns' => 'urn:xmpp:rayo:cpa:0'),
            '0342' => array('category' => 'rayo',       'ns' => 'urn:xmpp:rayo:fax:1'),
            '0347' => array('category' => 'iot',        'ns' => 'urn:xmpp:iot:discovery'),
            '0348' => array('category' => 'client',     'ns' => 'urn:xmpp:xdata:signature:oauth1'),
            );
    }

    function isImplemented($client, $key) {
        //var_dump($client);
        if(array_search($this->_nslist[$key]['ns'], $client))
            return '<td class="yes '.$this->_nslist[$key]['category'].'">'.$key.'</td>';
        else
            return '<td class="no  '.$this->_nslist[$key]['category'].'">'.$key.'</td>';
    }

    function display()
    {
        $this->view->assign('table', $this->_table);
        $this->view->assign('nslist', $this->_nslist);
    }
}
