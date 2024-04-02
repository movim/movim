<?php

namespace Moxl\Xec\Payload;

use Movim\Session;
use Moxl\Authentication;
use Moxl\Stanza\Stream;

class StreamFeatures extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        if ($stanza->authentication && $stanza->authentication->attributes()->xmlns == 'urn:xmpp:sasl:2') {
            $mechanisms = (array)$stanza->authentication->mechanism;

            $channelBindings = [];

            if ($stanza->{'sasl-channel-binding'} && $stanza->{'sasl-channel-binding'}->attributes()->xmlns == 'urn:xmpp:sasl-cb:0') {
                foreach ($stanza->{'sasl-channel-binding'}->{'channel-binding'} as $channelBinding) {
                    array_push($channelBindings, (string)$channelBinding->attributes()->type);
                }
            }

            $session = Session::start();

            if ($session->get('password')) {
                if (!is_array($mechanisms)) {
                    $mechanisms = [$mechanisms];
                }

                $auth = Authentication::getInstance();
                $auth->choose($mechanisms, $channelBindings);

                Stream::bind2Set($auth->getType(), $auth->getResponse(), APP_TITLE . '.' . \generateKey(6));
            }

        } elseif ($stanza->mechanisms && $stanza->mechanisms->attributes()->xmlns = 'urn:ietf:params:xml:ns:xmpp-sasl') {
            (new SASL)->handle($stanza->mechanisms, $stanza);
        }
    }
}
