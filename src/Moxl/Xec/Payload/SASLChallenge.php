<?php

namespace Moxl\Xec\Payload;

use \SASL2\SASL2;

use Movim\Session;

class SASLChallenge extends Payload
{
    public function handle($stanza, $parent = false)
    {
        $sess = Session::start();

        $s = new SASL2;

        switch($sess->get('mecchoice')) {
            case 'SCRAMSHA1' :
                $fa = $sess->get('saslfa');
                $sess->remove('saslfa');
                $challenge = base64_decode((string)$stanza);

                \Moxl\Utils::log("/// SECOND MESSAGE - PROOF");

                $response = base64_encode($fa->getResponse($sess->get('username'), $sess->get('password'), $challenge));

                $xml = '<response xmlns="urn:ietf:params:xml:ns:xmpp-sasl">'.$response.'</response>';
                \Moxl\API::request($xml);

                break;

            case 'DIGESTMD5' :
                $decoded = base64_decode((string)$stanza);
                $s = new SASL2;
                $d = $s->factory('digest-md5');

                if(!$sess->get('saslfirst')) {
                    \Moxl\Utils::log("/// CHALLENGE");

                    $response = $d->getResponse(
                        $sess->get('username'),
                        $sess->get('password'),
                        $decoded,
                        $sess->get('host'),
                        'xmpp');

                    $response = base64_encode($response);

                    $xml = '<response xmlns="urn:ietf:params:xml:ns:xmpp-sasl">'.$response.'</response>';

                    $sess->set('saslfirst', 1);
                } else {
                    $xml = '<response xmlns="urn:ietf:params:xml:ns:xmpp-sasl"/>';
                }

                \Moxl\API::request($xml);

                break;

            case 'CRAMMD5' :
                $decoded = base64_decode((string)$stanza);

                $s = new SASL2;
                $c = $s->factory('cram-md5');

                $response = $c->getResponse($sess->get('username'), $sess->get('password'), $decoded);
                $response = base64_encode($response);

                \Moxl\Utils::log("/// CHALLENGE");

                $xml = '<response xmlns="urn:ietf:params:xml:ns:xmpp-sasl">'.$response.'</response>';

                \Moxl\API::request($xml);

                break;
        }
    }
}
