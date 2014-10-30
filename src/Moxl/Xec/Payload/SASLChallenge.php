<?php
/*
 * @file SASLChallenge.php
 * 
 * @brief Handle incoming SASL challenge
 * 
 * Copyright 2014 edhelas <edhelas@edhelas-laptop>
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 * 
 * 
 */

namespace Moxl\Xec\Payload;

use \SASL2\SASL2;

class SASLChallenge extends Payload
{
    public function handle($stanza, $parent = false) {        
        $sess = \Session::start();
        $session = \Sessionx::start();

        $s = new SASL2;

        switch($sess->get('mecchoice')) {
            case 'SCRAMSHA1' :
                $fa = $sess->get('saslfa');
                $challenge = base64_decode((string)$stanza);

                \Moxl\Utils::log("/// SECOND MESSAGE - PROOF");

                $response = base64_encode($fa->getResponse($session->user, $session->password, $challenge));
                
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
                        $session->user,
                        $session->password,
                        $decoded,
                        $session->host,
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

                $session = \Sessionx::start();
                
                $response = $c->getResponse($session->user, $session->pass, $decoded);
                $response = base64_encode($response);

                \Moxl\Utils::log("/// CHALLENGE");

                $xml = '<response xmlns="urn:ietf:params:xml:ns:xmpp-sasl">'.$response.'</response>';

                \Moxl\API::request($xml);

                break;
        }
    }
}
