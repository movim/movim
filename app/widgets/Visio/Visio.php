<?php

/**
 * @package Widgets
 *
 * @file Visio.php
 * This file is part of Movim.
 * 
 * @brief A jabber chat widget.
 *
 * @author Timothée Jaussoin
 * 
 * See COPYING for licensing information.
 */
 
//require_once(APP_PATH . "widgets/ChatExt/ChatExt.php");
use Moxl\Xec\Action\Jingle\SessionInitiate;
use Moxl\Xec\Action\Jingle\SessionTerminate;

class Visio extends WidgetBase
{
    function load()
    {
        $this->addcss('visio.css');
        $this->addjs('visio.js');
        $this->addjs('adapter.js');
        $this->addjs('webrtc.js');
        $this->addjs('turn.js');

        $this->registerEvent('jinglesessioninitiate',   'onSessionInitiate');
        $this->registerEvent('jingle_sessioninitiate_erroritemnotfound',   'onInitiationError');
        $this->registerEvent('jingle_sessioninitiate_errorunexpectedrequest',   'onInitiationError');
        $this->registerEvent('jinglesessionterminate',  'onSessionTerminate');
        $this->registerEvent('jinglesessionaccept',     'onSessionAccept');
        $this->registerEvent('jingletransportinfo',     'onTransportInfo');
        
        $this->registerEvent('jinglecreationsuccess',     'onCreationSuccess');
    }

    function onInitiationError() {
        RPC::call('sendTerminate');
        RPC::call('terminate');
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
            //RPC::call('Popup.setJid', (string)$jingle->attributes()->initiator);
            RPC::call('onOffer', $sdp);
        }
    }
    
    function onSessionAccept($jingle) {
        $jts = new \JingletoSDP($jingle);
        $sdp = $jts->generate();
        $sid = $jts->getSessionId();
        
        RPC::call('onAccept', $sdp);
        
        $s = Session::start('movim');
        $s->set('jingleSid', $sid);        
    }
    
    function onTransportInfo($jingle) {
        $jts = new \JingletoSDP($jingle);
        
        RPC::call('onCandidate', $jts->generate(), $jts->media);
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

        RPC::call('terminate');
        RPC::call('movim_fill', 'status', $message);
    }

    function ajaxSendProposal($proposal) {
        $p = json_decode($proposal);

        $sd = Sessionx::start();
        
        $stj = new SDPtoJingle(
            $p->sdp,
            $this->user->getLogin().'/'.$sd->resource,
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
        $s = Session::start();
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
    }

    function ajaxGetContact($jid)
    {
        $cd = new \Modl\ContactDAO();
        $contact = $cd->get($jid);

        $contactview = $this->tpl();
        $contactview->assign('contact', $contact);

        RPC::call('movim_fill', 'avatar', $contactview->draw('_visio_contact', true));
    }

    function display()
    {
        //if(isset($_GET['f'])) {
        //    list($jid, $resource) = explode('/', htmlentities($_GET['f']));

        $json = requestURL('https://computeengineondemand.appspot.com/turn?username=93773443&key=4080218913', 1);
        $this->view->assign('turn_list'   , $json);
            
        /*    $cd = new \Modl\ContactDAO();
            $contact = $cd->get($jid);

            if(!$contact)
                $contact = new modl\Contact();

            $this->view->assign('avatar',$contact->getPhoto('l'));
            $this->view->assign('name'  ,$contact->getTrueName());
            $this->view->assign('jid'   ,$jid);
            $this->view->assign('resource'   ,$resource);
        }*/
    }
}
