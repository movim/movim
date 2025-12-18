<?php

namespace Moxl\Stanza;

class Register
{
    public static function get()
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $query = $dom->createElementNS('jabber:iq:register', 'query');

        return $query;
    }

    public static function set($data)
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

        return $query;
    }

    public static function remove()
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $query = $dom->createElementNS('jabber:iq:register', 'query');
        $query->appendChild($dom->createElement('remove'));

        return $query;
    }

    public static function changePassword($username, $password)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $query = $dom->createElementNS('jabber:iq:register', 'query');

        $usernameNode = $dom->createElement('username');
        $usernameNode->appendChild($dom->createTextNode($username));
        $query->appendChild($usernameNode);

        $passwordNode = $dom->createElement('password');
        $passwordNode->appendChild($dom->createTextNode($password));
        $query->appendChild($passwordNode);

        return $query;
    }
}
