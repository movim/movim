<?php

namespace Moxl\Stanza;

use Moxl\Stanza\Message;

use Movim\Session;
use App\MessageOmemoHeader;

class Muc
{
    public static function message($to, $content = false, $html = false, $id = false,
        $replace = false, $file = false, $parentId = false, array $reactions = [],
        $originId = false, $threadId = false, $mucReceipts = false, $replyId = false,
        $replyTo = false, $replyQuotedBodyLength = 0,
        ?MessageOmemoHeader $messageOMEMO = null)
    {
        Message::maker(
            $to,
            $content,
            $html,
            'groupchat',
            $mucReceipts, // chatstates required as well
            $mucReceipts ? 'request' : false,
            $id,
            $replace,
            $file,
            false,
            $parentId,
            $reactions,
            $originId,
            $threadId,
            $replyId,
            $replyTo,
            $replyQuotedBodyLength,
            $messageOMEMO);
    }

    public static function active($to)
    {
        Message::maker($to, false, false, 'groupchat', 'active');
    }

    public static function inactive($to)
    {
        Message::maker($to, false, false, 'groupchat', 'inactive');
    }

    public static function composing($to)
    {
        Message::maker($to, false, false, 'groupchat', 'composing');
    }

    public static function paused($to)
    {
        Message::maker($to, false, false, 'groupchat', 'paused');
    }

    public static function setSubject($to, $subject)
    {
        $session = Session::start();

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $message = $dom->createElementNS('jabber:client', 'message');
        $dom->appendChild($message);
        $message->setAttribute('to', str_replace(' ', '\40', $to));
        $message->setAttribute('id', $session->get('id'));
        $message->setAttribute('type', 'groupchat');

        $message->appendChild($dom->createElement('subject', $subject));

        \Moxl\API::request($dom->saveXML($dom->documentElement));
    }

    public static function destroy($to)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $query = $dom->createElementNS('http://jabber.org/protocol/muc#owner', 'query');

        $destroy = $dom->createElement('destroy');
        $destroy->setAttribute('jid', $to);
        $query->appendChild($destroy);

        $xml = \Moxl\API::iqWrapper($query, $to, 'set');
        \Moxl\API::request($xml);
    }

    public static function setRole($to, $nick, $role)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $query = $dom->createElementNS('http://jabber.org/protocol/muc#admin', 'query');

        $item = $dom->createElement('item');
        $item->setAttribute('nick', $nick);
        $item->setAttribute('role', $role);
        $query->appendChild($item);

        $xml = \Moxl\API::iqWrapper($query, $to, 'set');
        \Moxl\API::request($xml);
    }

    public static function getConfig($to)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $query = $dom->createElementNS('http://jabber.org/protocol/muc#owner', 'query');

        $xml = \Moxl\API::iqWrapper($query, $to, 'get');
        \Moxl\API::request($xml);
    }

    public static function setConfig($to, $data)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $query = $dom->createElementNS('http://jabber.org/protocol/muc#owner', 'query');
        $dom->appendChild($query);

        $x = $dom->createElementNS('jabber:x:data', 'x');
        $x->setAttribute('type', 'submit');
        $query->appendChild($x);

        $xmpp = new \FormtoXMPP($data);
        $xmpp->create();
        $xmpp->appendToX($dom);

        $xml = \Moxl\API::iqWrapper($query, $to, 'set');
        \Moxl\API::request($xml);
    }

    public static function changeAffiliation(string $to, string $jid, string $affiliation, ?string $reason)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $query = $dom->createElementNS('http://jabber.org/protocol/muc#admin', 'query');
        $dom->appendChild($query);

        $item = $dom->createElement('item');
        $item->setAttribute('affiliation', $affiliation);
        $item->setAttribute('jid', $jid);
        $query->appendChild($item);

        if ($reason) {
            $reason = $dom->createElement('reason', $reason);
            $item->appendChild($reason);
        }

        $xml = \Moxl\API::iqWrapper($query, $to, 'set');
        \Moxl\API::request($xml);
    }

    public static function createGroupChat($to, $name)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $query = $dom->createElementNS('http://jabber.org/protocol/muc#owner', 'query');
        $dom->appendChild($query);

        $x = $dom->createElementNS('jabber:x:data', 'x');
        $x->setAttribute('type', 'submit');
        $query->appendChild($x);

        $field = $dom->createElement('field');
        $field->setAttribute('var', 'FORM_TYPE');
        $x->appendChild($field);
        $value = $dom->createElement('value', 'http://jabber.org/protocol/muc#roomconfig');
        $field->appendChild($value);

        $field = $dom->createElement('field');
        $field->setAttribute('var', 'muc#roomconfig_roomname');
        $x->appendChild($field);
        $value = $dom->createElement('value', $name);
        $field->appendChild($value);

        $field = $dom->createElement('field');
        $field->setAttribute('var', 'muc#roomconfig_persistentroom');
        $x->appendChild($field);
        $value = $dom->createElement('value', 'true');
        $field->appendChild($value);

        $field = $dom->createElement('field');
        $field->setAttribute('var', 'muc#roomconfig_changesubject');
        $x->appendChild($field);
        $value = $dom->createElement('value', 'false');
        $field->appendChild($value);

        $field = $dom->createElement('field');
        $field->setAttribute('var', 'muc#roomconfig_membersonly');
        $x->appendChild($field);
        $value = $dom->createElement('value', 'true');
        $field->appendChild($value);

        $field = $dom->createElement('field');
        $field->setAttribute('var', 'muc#roomconfig_whois');
        $x->appendChild($field);
        $value = $dom->createElement('value', 'anyone');
        $field->appendChild($value);

        $field = $dom->createElement('field');
        $field->setAttribute('var', 'muc#roomconfig_publicroom');
        $x->appendChild($field);
        $value = $dom->createElement('value', 'false');
        $field->appendChild($value);

        /*$field = $dom->createElement('field');
        $field->setAttribute('var', 'muc#roomconfig_allowpm');
        $x->appendChild($field);
        $value = $dom->createElement('value', 'none');
        $field->appendChild($value);*/

        $xml = \Moxl\API::iqWrapper($query, $to, 'set');
        \Moxl\API::request($xml);
    }

    public static function createChannel($to, $name)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $query = $dom->createElementNS('http://jabber.org/protocol/muc#owner', 'query');
        $dom->appendChild($query);

        $x = $dom->createElementNS('jabber:x:data', 'x');
        $x->setAttribute('type', 'submit');
        $query->appendChild($x);

        $field = $dom->createElement('field');
        $field->setAttribute('var', 'FORM_TYPE');
        $x->appendChild($field);
        $value = $dom->createElement('value', 'http://jabber.org/protocol/muc#roomconfig');
        $field->appendChild($value);

        $field = $dom->createElement('field');
        $field->setAttribute('var', 'muc#roomconfig_roomname');
        $x->appendChild($field);
        $value = $dom->createElement('value', $name);
        $field->appendChild($value);

        $field = $dom->createElement('field');
        $field->setAttribute('var', 'muc#roomconfig_persistentroom');
        $x->appendChild($field);
        $value = $dom->createElement('value', 'true');
        $field->appendChild($value);

        $field = $dom->createElement('field');
        $field->setAttribute('var', 'muc#roomconfig_changesubject');
        $x->appendChild($field);
        $value = $dom->createElement('value', 'false');
        $field->appendChild($value);

        $field = $dom->createElement('field');
        $field->setAttribute('var', 'muc#roomconfig_membersonly');
        $x->appendChild($field);
        $value = $dom->createElement('value', 'false');
        $field->appendChild($value);

        $field = $dom->createElement('field');
        $field->setAttribute('var', 'muc#roomconfig_whois');
        $x->appendChild($field);
        $value = $dom->createElement('value', 'moderators');
        $field->appendChild($value);

        $field = $dom->createElement('field');
        $field->setAttribute('var', 'muc#roomconfig_publicroom');
        $x->appendChild($field);
        $value = $dom->createElement('value', 'true');
        $field->appendChild($value);

        /*$field = $dom->createElement('field');
        $field->setAttribute('var', 'muc#muc#roomconfig_allowpm');
        $x->appendChild($field);
        $value = $dom->createElement('value', 'anyone');
        $field->appendChild($value);*/

        $xml = \Moxl\API::iqWrapper($query, $to, 'set');
        \Moxl\API::request($xml);
    }

    public static function getMembers($to, $affiliation)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $query = $dom->createElementNS('http://jabber.org/protocol/muc#admin', 'query');

        $item = $dom->createElement('item');
        $item->setAttribute('affiliation', $affiliation);
        $query->appendChild($item);

        $xml = \Moxl\API::iqWrapper($query, $to, 'get');
        \Moxl\API::request($xml);
    }
}
