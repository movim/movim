<?php

namespace Moxl\Stanza;

class Register
{
    public static function get($to = false)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $query = $dom->createElementNS('jabber:iq:register', 'query');

        \Moxl\API::request(\Moxl\API::iqWrapper($query, $to, 'get'));
    }

    public static function set($to = false, $data)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $query = $dom->createElementNS('jabber:iq:register', 'query');

        // https://xmpp.org/extensions/xep-0077.html#usecases-register
        if (substr(array_key_first($data), 0, 8) == 'generic_') {
            foreach ($data as $key => $value) {
                $query->appendChild($dom->createElement(substr($key, 8), $value));
            }
        } else {
            $x = $dom->createElement('x');
            $x->setAttribute('xmlns', 'jabber:x:data');
            $x->setAttribute('type', 'submit');
            $query->appendChild($x);

            \Moxl\Utils::injectConfigInX($x, $data);
        }

        \Moxl\API::request(\Moxl\API::iqWrapper($query, $to, 'set'));
    }

    public static function remove(?string $to)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $query = $dom->createElementNS('jabber:iq:register', 'query');
        $query->appendChild($dom->createElement('remove'));

        \Moxl\API::request(\Moxl\API::iqWrapper($query, $to ?? false, 'set'));
    }

    public static function changePassword($to, $username, $password)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $query = $dom->createElementNS('jabber:iq:register', 'query');

        $usernameNode = $dom->createElement('username');
        $usernameNode->appendChild($dom->createTextNode($username));
        $query->appendChild($usernameNode);

        $passwordNode = $dom->createElement('password');
        $passwordNode->appendChild($dom->createTextNode($password));
        $query->appendChild($passwordNode);

        \Moxl\API::request(\Moxl\API::iqWrapper($query, $to, 'set'));
    }
}
