<?php

namespace Moxl;

function boshWrapper($xml, $type = false)
{
    $session = \Sessionx::start();

    $typehtml = '';
    if($type != false)
        $typehtml = ' type="'.$type.'" ';

    return '
        <body
            rid="'.$session->rid.'"
            sid="'.$session->sid.'"
            '.$typehtml.'
            xmlns="http://jabber.org/protocol/httpbind">
            '.$xml.'
        </body>';
}

function iqWrapper($xml, $to = false, $type = false)
{
    $session = \Sessionx::start();
    
    $toxml = $typexml = '';
    if($to != false)
        $toxml = 'to="'.$to.'"';
    if($type != false)
        $typexml = 'type="'.$type.'"';

    global $language;

    return '
        <iq
            id="'.$session->id.'"
            from="'.$session->user.'@'.$session->host.'/'.$session->ressource.'"
            xml:lang="'.$language.'"
            xmlns="jabber:client"
            '.$toxml.'
            '.$typexml.'>
            '.$xml.'
        </iq>
    ';
}

function login()
{
    $session = \Sessionx::start();
    
    Utils::log("/// STREAM INIT");

        $xml = '
            <body
                content="text/xml; charset=utf-8"
                hold="1"
                xmlns="http://jabber.org/protocol/httpbind"
                wait="30"
                rid="'.$session->rid.'"
                version="1.6"
                polling="0"
                secure="true"
                xmlns:xmpp="urn:xmpp:xbosh"
                to="'.$session->host.'"
                route="xmpp:'.$session->domain.':'.$session->port.'"
                xmpp:version="1.0"
            />';

        $xml = launch($xml, false);

        $xmle = new \SimpleXMLElement($xml['content']);

        $session->sid = (string)$xmle->attributes()->sid;

        // We search the presence of a restartlogic attribute
        
        $restart = @$xmle->xpath('@xmpp:restartlogic');
        $restart = (array)$restart[0];

        if($restart['@attributes']['restartlogic'] == true) {
            $xml = boshWrapper('', false, true);

            $xml = launch($xml, false);
            $xmle = new \SimpleXMLElement($xml['content']);
        }
        
        if($xmle->head || (string)$xmle->attributes()->type == 'terminate')
            return 'bosherror';

        if(isset($xmle->streamfeatures)) {
            $mec = (array)$xmle->streamfeatures->mechanisms;
            $mec = $mec['mechanism'];

            if(!is_array($mec))
                $mec = array($mec);
        } else {
            return 'mecerror';
        }
        
        $mecchoice = str_replace('-', '', mechanismChoice($mec));

        //Special case for all apinc.org domains (jabber.org...)
        if($session->host == 'jabber.org')
            $mecchoice = 'CRAMMD5';

        Utils::log("/// MECANISM CHOICE ".$mecchoice);

        if(function_exists('moxl\mechanism'.$mecchoice)) {
            $return = call_user_func('moxl\mechanism'.$mecchoice);
        } else
            return 'errormechanism';

        if($return != 'OK')
            return $return;

        restartRequest();
        $return = ressourceRequest();

        if($return != 'OK')
            return $return;
        
        /*if(in_array('SCRAM-SHA-1', $mec)) {
            Utils::log("/// MECANISM CHOICE DIGEST-MD5");
                $response = base64_encode(
                                'n,,n='.$session['user'].',r=d2fc512490a15036460b5489401439d6da5407fa');

                $xml = boshWrapper(
                        '<auth xmlns="urn:ietf:params:xml:ns:xmpp-sasl" mechanism="SCRAM-SHA-1">
                            '.$response.'
                        </auth>');

                $r = new MoxlRequest($xml);
                $xml = $r->fire();

                $xmle = new \SimpleXMLElement($xml['content']);
                if($xmle->failure)
                    return 'errormechanism';

                $decoded = base64_decode((string)$xmle->challenge);
                \movim_log($decoded);
                $arr = explode(',', $decoded);
                \movim_log($arr);

                //pbkdf2($session['password'], $decoded['s'], $decoded['i']);

                //$response = base64_encode(
                //                'c=biws, '.$arr[1].',p=d2fc512490a15036460b5489401439d6da5407fa');

            exit;
        } else*/

    Utils::log("/// START THE SESSION");

        $xml = boshWrapper(
            '<iq
                type="set"
                id="'.$session->id.'"
                to="'.$session->host.'">
                <session xmlns="urn:ietf:params:xml:ns:xmpp-session"/>
            </iq>', false);

        $xml = launch($xml, false);

    Utils::log("/// AUTH SUCCESSFULL");

    $session->active = true;
    $session->password = 'hidden';

    // We get the general configuration

    $s = new Xec\Action\Storage\Get();
    $s->setXmlns('movim:prefs')
      ->request();
      
    // http://xmpp.org/extensions/xep-0280.html
    \Moxl\Stanza\enableCarbons();

    // We refresh the bookmarks
    $b = new \Moxl\Xec\Action\Bookmark\Get;
    $b->setTo($session->user.'@'.$session->host)
      ->request();

    // We grab the precedente presence from the Cache and send it !
    $presence = \Cache::c('presence');

    if(!isset($presence['show']) || $presence['show'] == '')
        $presence['show'] = 'chat';

    if(!isset($presence['status']) || $presence['status'] == '')
        $presence['status'] = 'Online with Movim';

    switch($presence['show']) {
        case 'chat':
            $p = new \Moxl\Xec\Action\Presence\Chat;
            $p->setStatus(htmlspecialchars($presence['status']))->request();
            break;
        case 'away':
            $p = new \Moxl\Xec\Action\Presence\Away;
            $p->setStatus(htmlspecialchars($presence['status']))->request();
            break;
        case 'dnd':
            $p = new \Moxl\Xec\Action\Presence\DND;
            $p->setStatus(htmlspecialchars($presence['status']))->request();
            break;
        case 'xa':
            $p = new \Moxl\Xec\Action\Presence\XA;
            $p->setStatus(htmlspecialchars($presence['status']))->request();
            break;
    }

    // Here we go !!!
    return 'OK';
}

/*
 *  Call the request class with the correct XML
 */
function request($xml, $type = false)
{
    $session = \Sessionx::start();

    if($session->active == true) {
        $sess = \Session::start(APP_NAME);
        $cached = $sess->get('moxlcache');
        
        if(!empty($cached)) {
            $r = cacheLoad($cached);
            $r = cacheSplit($r);

            Handler::handle($r);
            $evt = new \Event();
            $evt->runEvent('incomingemptybody', 'ping');
            $evt->runEvent('connection', count($cached));
        } else {
            $xml = boshWrapper($xml, $type);

            launch($xml, true);
        }
    } else {
        Utils::log(
            "!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!\n"
            ."Session unstarted, please login\n"
            ."!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!");
        header(':', true,'400');
        exit;
    }
}

/*
 * Fire the request and detect if an error occure, if not handle it
 */
function launch($xml, $handle = true)
{
    $r = new Request($xml);
    $r->fire();
    
    // Choose to handle the XMPP stanza or just return it for specific treatment
    if($r->state == 'ok') {
        if($handle)
            handle($r->xmlr);
        else
            return $r->xmlr;
    } elseif($r->state == 'error') {
        handleError($r->error_number, $r->error_message);
        exit;
    }
}

/*
 * A simple ping to the XMPP BOSH
 */
function ping() { request(''); }

function cacheSplit($r) 
{
    $tohandle = array_slice($r, 0, 9);
    $tocache  = array_slice($r, 10);
    
    $tocaches = array();
    foreach($tocache as $value) {
        array_push($tocaches, $value->asXML());
    }
    
    $sess = \Session::start(APP_NAME);
    $sess->set('moxlcache', $tocaches); 
    
    return $tohandle;
}

function cacheLoad($r)
{
    $result = array();
    $result = array_map('simplexml_load_string', $r);
    
    return $result;
}

/*
 * Handle each request and send it to XEC
 */    
function handle($callback)
{
    if(isset($callback['content']) && $callback['content'] != '') {
        // Convert it to a SimpleXMLElement
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($callback['content']);

        if($xml === false) {
            $errors = '';
            foreach(libxml_get_errors() as $error) {
                $errors .=  $error->message;
            }
        
            Utils::log(
            "!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!\n"
            ."Session droped\n"
            .$errors            
            ."!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!");
            
            $sess = \Session::start(APP_NAME);
            $sess->dispose(APP_NAME);

            header(':', true,'500');

            exit;
        } else { 
            $xmle = new \SimpleXMLIterator($callback['content']);

            if($xmle instanceof \SimpleXMLIterator) {    
                $r = array();
                
                for( $xmle->rewind(); $xmle->valid(); $xmle->next() ) 
                    array_push($r, $xmle->current());

                if(count($r) > 10) 
                    $r = cacheSplit($r);
                
                Handler::handle($r);
                $evt = new \Event();
                $evt->runEvent('incomingemptybody', 'ping');
            }
        }
    }
}

/*
 * Handle requests errors
 */
function handleError($number, $message)
{
    Handler::handleError($number, $message);
}
