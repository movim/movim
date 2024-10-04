<?php

namespace Moxl;

use Fabiang\Sasl\Sasl;
use Movim\Session;

class Authentication
{
    private $_mechanism;
    private ?string $_type;

    protected static $instance;

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new Authentication;
        }

        return self::$instance;
    }

    public function choose(array $mechanisms, array $channelBindings = [])
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
                $session = Session::instance();

                $this->_mechanism = $factory->factory($this->_type, [
                    'authcid'  => $session->get('username'),
                    'secret'   => $session->get('password'),
                    'downgrade_protection' => [
                        'allowed_mechanisms'       => $mechanisms,
                        'allowed_channel_bindings' => $channelBindings
                    ],
                ]);

                break;
            }
        }
    }

    public function getType(): string
    {
        return $this->_type;
    }

    public function getResponse(): string
    {
        return $this->_mechanism->createResponse();
    }

    public function response()
    {
        $response = base64_encode($this->getResponse());

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
