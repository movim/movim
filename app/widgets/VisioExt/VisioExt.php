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

use Moxl\Xec\Action\Jingle\SessionInitiate;
use Moxl\Xec\Action\Jingle\SessionTerminate;

class VisioExt extends WidgetBase
{
    function load() {
        $this->addjs('visioext.js');
        $this->registerEvent('jinglesessioninitiate',   'onSessionInitiate');
        /*$this->registerEvent('jinglesessionterminate',  'onSessionTerminate');
        $this->registerEvent('jinglesessionaccept',     'onSessionAccept');
        $this->registerEvent('jingletransportinfo',     'onTransportInfo');
        
        $this->registerEvent('jinglecreationsuccess',     'onCreationSuccess');*/
    }
    
    function onSessionInitiate($jingle) {
        $jts = new \JingletoSDP($jingle);
        $sdp = $jts->generate();

        $cd = new \Modl\ContactDAO();
        $contact = $cd->get(cleanJid((string)$jingle->attributes()->initiator));

        if(!isset($contact))
            $contact = new Modl\Contact;
        
        if($sdp) {
            RPC::call(
                'movim_desktop_notification',
                $contact->getTrueName(),
                $this->__('visio.calling'),
                $contact->getPhoto('m'));
            RPC::call('remoteSetJid', (string)$jingle->attributes()->initiator);
            RPC::call('remoteCall', 'onOffer', $sdp);
            RPC::commit();
        }
    }
    /*
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
                $message = $this->__('visio.hung_up');
                break;
                
            case 'busy':
                $message = $this->__('visio.busy');
                break;
                
            case 'decline':
                $message = $this->__('visio.declined');
                break;
                
            case 'unsupported-transports':

                break;
                
            case 'failed-transport':

                break;
                
            case 'unsupported-applications':

                break;
                
            case 'failed-application':
                $message = $this->__('visio.remote_incompatible');
                break;
                
            case 'incompatible-parameters':

                break;

            default:
                $message = $this->__('visio.unknown_error');
                break;
        }

        RPC::call('Popup.call', 'terminate');
        RPC::call('Popup.call', 'movim_fill', 'status', $message);
    }

    function ajaxSendProposal($proposal) {
        $p = json_decode($proposal);

        $sd = Sessionx::start();
        
        $stj = new SDPtoJingle(
            $p->sdp,
            $this->user->getLogin().'/'.$sd->refsource,
            $p->jid.'/'.$p->resource,
            'session-initiate');
        
        $r = new SessionInitiate;
        $r->setTo($p->jid.'/'.$p->resource)
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
            $this->user->getLogin().'/'.$sd->resource,
            $p->jid.'/'.$p->resource,
            'session-accept');
            
        $r = new SessionInitiate;
        $r->setTo($p->jid.'/'.$p->resource)
          ->setOffer($stj->generate())
          ->request();
    }

    function ajaxSendSessionTerminate($jid, $resource, $reason = null) {
        $s = Session::start('movim');
        $jingleSid = $s->get("jingleSid");
        
        $r = new SessionTerminate;
        $r->setTo($jid.'/'.$resource);
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
            $this->user->getLogin().'/'.$sd->resource,
            $p->jid.'/'.$p->resource,
            'transport-info');

        $r = new SessionInitiate;
        $r->setTo($p->jid.'/'.$p->resource)
          ->setOffer($stj->generate())
          ->request();
    }*/

    function build() {

    }
}
