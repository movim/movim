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

        $xml =  '<auth xmlns="urn:ietf:params:xml:ns:xmpp-sasl" mechanism="PLAIN">'.
                    $response.
                '</auth>';

        API::request($xml);
    }

    static function mechanismANONYMOUS() {
        $session = \Sessionx::start();
        
        $s = new SASL2;
        $fa = $s->factory('ANONYMOUS');

        $xml =  '<auth xmlns="urn:ietf:params:xml:ns:xmpp-sasl" mechanism="ANONYMOUS"/>';

        API::request($xml);
    }

    static function mechanismDIGESTMD5() {
        $xml =  '<auth 
                    client-uses-full-bind-result="true"
                    xmlns="urn:ietf:params:xml:ns:xmpp-sasl" 
                    mechanism="DIGEST-MD5"/>';

        API::request($xml);
    }

    static function mechanismCRAMMD5() {
        $xml =  '<auth 
                    client-uses-full-bind-result="true"
                    xmlns="urn:ietf:params:xml:ns:xmpp-sasl" 
                    mechanism="CRAM-MD5"/>';

        API::request($xml);
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
}
