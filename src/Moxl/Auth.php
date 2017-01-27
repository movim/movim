<?php

namespace Moxl;

use \SASL2\SASL2;

class Auth
{
    static function mechanismChoice($mec)
    {
        $mechanism = [
                        'SCRAM-SHA-1',
                        'DIGEST-MD5',
                        'CRAM-MD5',
                        'PLAIN',
                        'ANONYMOUS'
                     ];

        $mecchoice = false;
        $i = 0;

        while($mecchoice == false && $i <= count($mechanism)) {
            if(in_array($mechanism[$i], $mec))
                $mecchoice = true;
            else $i++;
        }

        $session = \Session::start();
        $session->set('mechanism', $mechanism[$i]);

        return $mechanism[$i];
    }

    static function mechanismPLAIN()
    {
        $session = \Session::start();

        $s = new SASL2;
        $p = $s->factory('plain');

        $response = base64_encode($p->getResponse($session->get('username'), $session->get('password')));

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $auth = $dom->createElementNS('urn:ietf:params:xml:ns:xmpp-sasl', 'auth', $response);
        $auth->setAttribute('mechanism', 'PLAIN');
        $dom->appendChild($auth);

        API::request($dom->saveXML($dom->documentElement));
    }

    static function mechanismANONYMOUS()
    {
        $s = new SASL2;
        $fa = $s->factory('ANONYMOUS');

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $auth = $dom->createElementNS('urn:ietf:params:xml:ns:xmpp-sasl', 'auth');
        $auth->setAttribute('mechanism', 'ANONYMOUS');
        $dom->appendChild($auth);

        API::request($dom->saveXML($dom->documentElement));
    }

    static function mechanismDIGESTMD5()
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $auth = $dom->createElementNS('urn:ietf:params:xml:ns:xmpp-sasl', 'auth');
        $auth->setAttribute('client-uses-full-bind-result', 'true');
        $auth->setAttribute('mechanism', 'DIGEST-MD5');
        $dom->appendChild($auth);

        API::request($dom->saveXML($dom->documentElement));

    }

    static function mechanismCRAMMD5()
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $auth = $dom->createElementNS('urn:ietf:params:xml:ns:xmpp-sasl', 'auth');
        $auth->setAttribute('client-uses-full-bind-result', 'true');
        $auth->setAttribute('mechanism', 'CRAM-MD5');
        $dom->appendChild($auth);

        API::request($dom->saveXML($dom->documentElement));
    }

    static function mechanismSCRAMSHA1()
    {
        $s = new SASL2;
        $fa = $s->factory('SCRAM-SHA1');

        $session = \Session::start();

        Utils::log("/// INITIAL MESSAGE");

        $response = base64_encode($fa->getResponse($session->get('username'), $session->get('password')));

        $session->set('saslfa', $fa);

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $auth = $dom->createElementNS('urn:ietf:params:xml:ns:xmpp-sasl', 'auth', $response);
        $auth->setAttribute('mechanism', 'SCRAM-SHA-1');
        $dom->appendChild($auth);

        API::request($dom->saveXML($dom->documentElement));

    }
}
