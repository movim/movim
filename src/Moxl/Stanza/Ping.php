<?php

namespace Moxl\Stanza;

function pingServer() {
    $session = \Sessionx::start();
    $xml = \Moxl\iqWrapper('<ping xmlns="urn:xmpp:ping"/>', $session->host, 'get');
    
    \Moxl\request($xml);
}
