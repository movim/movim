<?php

namespace Moxl;

use \SASL2\SASL2;

class Auth {
    static function mechanismChoice($mec) {
        $mechanism = array(
                        'SCRAM-SHA-1',
                        'DIGEST-MD5',
                        'CRAM-MD5',
                        'PLAIN',
                        'ANONYMOUS'
                        );
        
        $mecchoice = false;
        $i = 0;
        
        while($mecchoice == false && $i <= count($mechanism)) {
            if(in_array($mechanism[$i], $mec))
                $mecchoice = true;
            else $i++;
        }

        return $mechanism[$i];
    }

    static function mechanismPLAIN() {
        $session = \Sessionx::start();

        $s = new SASL2;
        $p = $s->factory('plain');
        
        $response = base64_encode($p->getResponse($session->user, $session->password));

        $xml =  '<auth xmlns="urn:ietf:params:xml:ns:xmpp-sasl" mechanism="PLAIN" client-uses-full-bind-result="true">'.
                    $response.
                '</auth>';

        API::request($xml);
    }

    static function mechanismANONYMOUS() {
        $s = new SASL2;
        $fa = $s->factory('ANONYMOUS');

        $session = \Sessionx::start();

        $xml = API::boshWrapper(
                '<auth xmlns="urn:ietf:params:xml:ns:xmpp-sasl" mechanism="ANONYMOUS"/>', false);

        $r = new Request($xml);
        $xml = $r->fire();

        $xmle = new \SimpleXMLElement($xml['content']);

        if(!$xmle->success)
            return 'failauth';
        else
            return 'OK';
    }

    static function mechanismDIGESTMD5() {
        $session = \Sessionx::start();
        
        $xml =  '<auth 
                    client-uses-full-bind-result="true"
                    xmlns="urn:ietf:params:xml:ns:xmpp-sasl" 
                    mechanism="DIGEST-MD5"/>';

        API::request($xml);
    }

    static function mechanismCRAMMD5() {
            $xml = API::boshWrapper(
                    '<auth 
                        client-uses-full-bind-result="true"
                        xmlns="urn:ietf:params:xml:ns:xmpp-sasl" 
                        mechanism="CRAM-MD5"/>', false);

            $r = new Request($xml);
            $xml = $r->fire();

            $xmle = new \SimpleXMLElement($xml['content']);
            if($xmle->failure)
                return 'errormechanism';

            $decoded = base64_decode((string)$xmle->challenge);

            if($decoded) {
                $s = new SASL2;
                $c = $s->factory('cram-md5');

                $session = \Sessionx::start();
                $response = $c->getResponse($session->user, $session->pass, $decoded);
                $response = base64_encode($response);
            } else
                return 'errorchallenge';

        Utils::log("/// CHALLENGE");

            $xml = API::boshWrapper(
                    '<response xmlns="urn:ietf:params:xml:ns:xmpp-sasl">'.$response.'</response>', false);

            $r = new Request($xml);
            $xml = $r->fire();

            $xmle = new \SimpleXMLElement($xml['content']);

        if(!$xmle->success)
            return 'failauth';
        else
            return 'OK';
    }

    static function mechanismSCRAMSHA1() {
            $s = new SASL2;
            $fa = $s->factory('SCRAM-SHA1');

            $session = \Sessionx::start();

        Utils::log("/// INITIAL MESSAGE");

            $response = base64_encode($fa->getResponse($session->user, $session->password));

            $sess = \Session::start();
            $sess->set('saslfa', $fa);

            $xml =  '<auth xmlns="urn:ietf:params:xml:ns:xmpp-sasl" mechanism="SCRAM-SHA-1">
                        '.$response.'
                    </auth>';

            API::request($xml);
    }
    
    static function restartRequest() {
        $session = \Sessionx::start();
        
        $xml =
            '<body
                rid="'.$session->rid.'"
                sid="'.$session->sid.'"
                to="'.$session->host.'"
                xml:lang="en"
                xmpp:restart="true"
                xmlns="http://jabber.org/protocol/httpbind"
                xmlns:xmpp="urn:xmpp:xbosh"/>';

        $r = new Request($xml);
        $xml = $r->fire();

        return $xml;
    }

    static function ressourceRequest() {
        $session = \Sessionx::start();
        
        $xml = API::boshWrapper(
            '<iq type="set" id="'.$session->id.'">
                <bind xmlns="urn:ietf:params:xml:ns:xmpp-bind">
                    <resource>'.$session->ressource.'</resource>
                </bind>
            </iq>', false, true);

        $r = new Request($xml);
        $xml = $r->fire();

        $xmle = new \SimpleXMLElement($xml['content']);

        if($xmle->head || (string)$xmle->attributes()->type == 'terminate')
            return 'failauth';
        elseif($xmle->iq->bind->jid) {
            list($jid, $ressource) = explode('/', (string)$xmle->iq->bind->jid);
            list($session->username, $session->host) = explode('@',$jid);
            if($ressource)
                $session->ressource = $ressource;
        }

        return 'OK';
    }

}
