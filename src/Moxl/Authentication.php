<?php

namespace Moxl;

use Fabiang\Sasl\Sasl;
use Movim\Session;

class Authentication
{
    private $_mechanism;
    private $_type;

    protected static $_instance;

    public static function getInstance()
    {
        if (!self::$_instance) {
            self::$_instance = new Authentication;
        }

        return self::$_instance;
    }

    public function choose($mechanisms)
    {
        $choices = [
            'SCRAM-SHA-1',
            'PLAIN',
            //'ANONYMOUS'
         ];

        foreach ($choices as $choice) {
            if (in_array($choice, $mechanisms)) {
                $this->_type = $choice;

                $factory = new Sasl;
                $session = Session::start();

                $this->_mechanism = $factory->factory($this->_type, [
                    'authcid'  => $session->get('username'),
                    'secret'   => $session->get('password')
                ]);

                break;
            }
        }
    }

    public function response()
    {
        $response = base64_encode($this->_mechanism->createResponse());

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $auth = $dom->createElementNS('urn:ietf:params:xml:ns:xmpp-sasl', 'auth', $response);
        $auth->setAttribute('mechanism', $this->_type);
        $dom->appendChild($auth);

        API::request($dom->saveXML($dom->documentElement));
    }

    public function challenge($challenge)
    {
        return $this->_mechanism->createResponse($challenge);
    }
}
