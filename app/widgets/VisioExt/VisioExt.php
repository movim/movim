<?php

/**
 * @package Widgets
 *
 * @file ChatExt.php
 * This file is part of MOVIM.
 * 
 * @brief A jabber chat widget.
 *
 * @author Guillaume Pasquet <etenil@etenilsrealm.nl>
 *
 * @version 1.0
 * @date 20 October 2010
 *
 * Copyright (C)2010 MOVIM project
 * 
 * See COPYING for licensing information.
 */

class VisioExt extends WidgetBase
{
    function WidgetLoad() {
        $this->addjs('visioext.js');
        $this->registerEvent('jinglesessioninitiate',   'onSessionInitiate');
        $this->registerEvent('jinglesessionterminate',  'onSessionTerminate');
        $this->registerEvent('jinglesessionaccept',     'onSessionAccept');
        $this->registerEvent('jingletransportinfo',     'onTransportInfo');
        
        $this->registerEvent('jinglecreationsuccess',     'onCreationSuccess');
    }
    
    function onSessionInitiate($jingle) {
        $jts = new \JingletoSDP($jingle);
        $sdp = $jts->generate();
        
        if($sdp) {        
            RPC::call('Popup.setJid', (string)$jingle->attributes()->initiator);
            RPC::call('Popup.call', 'onOffer', $sdp);
        }
    }
    
    function onSessionAccept($jingle) {
        $jts = new \JingletoSDP($jingle);
        $sdp = $jts->generate();
        $sid = $jts->getSessionId();
        
        RPC::call('Popup.call', 'onAccept', $sdp);
        
        $s = Session::start('movim');
        $s->set('jingleSid', $sid);        
    }
    
    function onTransportInfo($jingle) {
        $jts = new \JingletoSDP($jingle);
        
        RPC::call('Popup.call', 'onCandidate', $jts->generate(), $jts->media);
    }
    
    function onSessionTerminate($jingle) {
        $message = '';
        
        switch($jingle->reason->children()->getName()) {
            case 'success':
                $message = t('Hung up');
                break;
                
            case 'busy':
                $message = t('Your contact is busy');
                break;
                
            case 'decline':
                $message = t('Declined');
                break;
                
            case 'unsupported-transports':

                break;
                
            case 'failed-transport':

                break;
                
            case 'unsupported-applications':

                break;
                
            case 'failed-application':
                $message = t('Remote application incompatible');
                break;
                
            case 'incompatible-parameters':

                break;

            default:
                $message = t('Unknown error');
                break;
        }
                
        RPC::call('Popup.call', 'movim_fill', 'status', $message);
        RPC::call('Popup.call', 'terminate');
    }

    function ajaxSendProposal($proposal) {
        $p = json_decode($proposal);

        $sd = Sessionx::start();
        
        $stj = new SDPtoJingle(
            $p->sdp,
            $this->user->getLogin().'/'.$sd->ressource,
            $p->jid.'/'.$p->ressource,
            'session-initiate');
        
        $r = new moxl\JingleSessionInitiate();
        $r->setTo($p->jid.'/'.$p->ressource)
          ->setOffer($stj->generate())
          ->request();
        
        $sid = $stj->getSessionId();
        $s = Session::start('movim');
        $s->set('jingleSid', $sid);    
    }

    function ajaxSendAcceptance($proposal) {
        $p = json_decode($proposal);

        $sd = Sessionx::start();
        
        $stj = new SDPtoJingle(
            $p->sdp,
            $this->user->getLogin().'/'.$sd->ressource,
            $p->jid.'/'.$p->ressource,
            'session-accept');
            
        $r = new moxl\JingleSessionInitiate();
        $r->setTo($p->jid.'/'.$p->ressource)
          ->setOffer($stj->generate())
          ->request();
    }

    function ajaxSendSessionTerminate($jid, $ressource, $reason = null) {
        $s = Session::start('movim');
        $jingleSid = $s->get("jingleSid");
        
        $r = new moxl\JingleSessionTerminate();
        $r->setTo($jid.'/'.$ressource);
        $r->setJingleSid($jingleSid);

        if(isset($reason))
            $r->setReason($reason);

        $r->request();
    }

    function ajaxSendCandidate($candidate) {
        $p = json_decode($candidate);
        $sd = Sessionx::start();

        $sdp =
            'm='.$p->mid."\n".
            $p->sdp;

        $stj = new SDPtoJingle(
            $sdp,
            $this->user->getLogin().'/'.$sd->ressource,
            $p->jid.'/'.$p->ressource,
            'transport-info');

        $r = new moxl\JingleSessionInitiate();
        $r->setTo($p->jid.'/'.$p->ressource)
          ->setOffer($stj->generate())
          ->request();
    }

    function build() {

    }    
}
