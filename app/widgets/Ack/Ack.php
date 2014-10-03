<?php

/**
 * @package Widgets
 *
 * @file Ack.php
 * This file is part of MOVIM.
 *
 * @brief Send ack for each incoming requests.
 *
 * @author Timothée Jaussoin <edhelas@gmail.com>
 *
 * @version 1.0
 *
 * Copyright (C)2013 MOVIM project
 *
 * See COPYING for licensing information.
 */

use Moxl\Xec\Action\Ack\Send;
use Moxl\Xec\Action\Disco\Request;
use Moxl\Stanza\Disco;
 
class Ack extends WidgetBase {
    function load()
    {
        $this->registerEvent('ack', 'onAckRequest');
        $this->registerEvent('discoinfo', 'onDiscoInfoRequest');
        $this->registerEvent('caps', 'onCapsRequest');
    }

    function display()
    {
        $this->view->assign('ack', 
                            $this->genCallAjax(
                                "ajaxAckRequest", 'to', 'id')
                        );
        $this->view->assign('discoinfo', 
                            $this->genCallAjax(
                                "ajaxDiscoInfoRequest", 'to', 'id')
                        );
        $this->view->assign('caps', 
                            $this->genCallAjax(
                                "ajaxCapsRequest", 'to', 'node')
                        );
    }
    
    function onAckRequest($ack) {
        $to = $ack[0];
        $id = $ack[1];
        RPC::call('ackRequest', $to, $id);
    }
    
    function onDiscoInfoRequest($packet) {
        $to = $packet->content[0];
        $id = $packet->content[1];
        RPC::call('discoInfoRequest', $to, $id);
    }
    
    function onCapsRequest($packet) {
        $to     = $packet->content[0];
        $node   = $packet->content[1];
        RPC::call('capsRequest', $to, $node);
    }
    
    function ajaxAckRequest($to, $id) {       
        $ack = new Send;
        $ack->setTo($to)
            ->setId($id)
            ->request();
    }
    
    function ajaxDiscoInfoRequest($to, $id) {       
        //Disco::answer($jid, $id);
    }
    
    function ajaxCapsRequest($to, $node) {       
        $d = new Request;
        $d->setTo($to)
          ->setNode($node)
          ->request();
    }
}
