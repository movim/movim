<?php

namespace Moxl;

use Fabiang\SASL\SASL;

class Authentication
{
    public ?string $username = null;
    public ?string $password = null;

    private $_mechanism;
    private ?string $_type;

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

                $this->_mechanism = SASL::fromString($this->_type)->mechanism([
                    'authcid'  => $this->username,
                    'secret'   => $this->password,
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

    public function response(): string
    {
        $response = base64_encode($this->getResponse());

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $auth = $dom->createElementNS('urn:ietf:params:xml:ns:xmpp-sasl', 'auth', $response);
        $auth->setAttribute('mechanism', $this->_type);
        $dom->appendChild($auth);

        return $dom->saveXML($dom->documentElement);
    }

    public function challenge($challenge)
    {
        return $this->_mechanism->createResponse($challenge);
    }

    public function clear()
    {
        $this->username = $this->password = null;
    }
}
