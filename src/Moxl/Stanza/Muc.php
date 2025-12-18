<?php

namespace Moxl\Stanza;

use App\MessageFile;
use Moxl\Stanza\Message;

use Movim\Session;
use App\MessageOmemoHeader;

class Muc
{
    /*public static function message(
        $to,
        $content = false,
        $html = false,
        $id = false,
        $replace = false,
        ?MessageFile $file = null,
        $parentId = false,
        array $reactions = [],
        $originId = false,
        $threadId = false,
        $mucReceipts = false,
        $replyId = false,
        $replyTo = false,
        $replyQuotedBodyLength = 0,
        ?MessageOmemoHeader $messageOMEMO = null
    ) {
        return Message::maker(
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
            $messageOMEMO
        );
    }*/

    public static function setSubject(string $to, string $subject)
    {
        $session = Session::instance();

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $message = $dom->createElementNS('jabber:client', 'message');
        $dom->appendChild($message);
        $message->setAttribute('to', str_replace(' ', '\40', $to));
        $message->setAttribute('id', $session->get('id'));
        $message->setAttribute('type', 'groupchat');

        $message->appendChild($dom->createElement('subject', $subject));

        return $dom;
    }

    public static function destroy($to)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $query = $dom->createElementNS('http://jabber.org/protocol/muc#owner', 'query');

        $destroy = $dom->createElement('destroy');
        $destroy->setAttribute('jid', $to);
        $query->appendChild($destroy);

        return $query;
    }

    public static function setRole($nick, $role)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $query = $dom->createElementNS('http://jabber.org/protocol/muc#admin', 'query');

        $item = $dom->createElement('item');
        $item->setAttribute('nick', $nick);
        $item->setAttribute('role', $role);
        $query->appendChild($item);

        return $query;
    }

    public static function getConfig()
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $query = $dom->createElementNS('http://jabber.org/protocol/muc#owner', 'query');

        return $query;
    }

    public static function setConfig(array $data)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $query = $dom->createElementNS('http://jabber.org/protocol/muc#owner', 'query');
        $dom->appendChild($query);

        $x = $dom->createElementNS('jabber:x:data', 'x');
        $x->setAttribute('type', 'submit');
        $query->appendChild($x);

        \Moxl\Utils::injectConfigInX($x, $data);

        return $query;
    }

    public static function changeAffiliation(string $jid, string $affiliation, ?string $reason)
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

        return $query;
    }

    public static function createMujiChat()
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $query = $dom->createElementNS('http://jabber.org/protocol/muc#owner', 'query');
        $dom->appendChild($query);

        $x = $dom->createElementNS('jabber:x:data', 'x');
        $x->setAttribute('type', 'submit');
        $query->appendChild($x);

        \Moxl\Utils::injectConfigInX($x, [
            'FORM_TYPE' => 'http://jabber.org/protocol/muc#roomconfig',
            'muc#roomconfig_persistentroom' => 'false',
            'muc#roomconfig_membersonly' => 'false',
            'muc#roomconfig_whois' => 'anyone',
        ]);

        return $query;
    }

    public static function createGroupChat($name)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $query = $dom->createElementNS('http://jabber.org/protocol/muc#owner', 'query');
        $dom->appendChild($query);

        $x = $dom->createElementNS('jabber:x:data', 'x');
        $x->setAttribute('type', 'submit');
        $query->appendChild($x);

        \Moxl\Utils::injectConfigInX($x, [
            'FORM_TYPE' => 'http://jabber.org/protocol/muc#roomconfig',
            'muc#roomconfig_roomname' => $name,
            'muc#roomconfig_persistentroom' => 'true',
            'muc#roomconfig_changesubject' => 'false',
            'muc#roomconfig_membersonly' => 'true',
            'muc#roomconfig_whois' => 'anyone',
            'muc#roomconfig_publicroom' => 'false',
            //'muc#roomconfig_allowpm' => 'false'
        ]);

        return $query;
    }

    public static function createChannel($name)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $query = $dom->createElementNS('http://jabber.org/protocol/muc#owner', 'query');
        $dom->appendChild($query);

        $x = $dom->createElementNS('jabber:x:data', 'x');
        $x->setAttribute('type', 'submit');
        $query->appendChild($x);

        \Moxl\Utils::injectConfigInX($x, [
            'FORM_TYPE' => 'http://jabber.org/protocol/muc#roomconfig',
            'muc#roomconfig_roomname' => $name,
            'muc#roomconfig_persistentroom' => 'true',
            'muc#roomconfig_changesubject' => 'false',
            'muc#roomconfig_membersonly' => 'false',
            'muc#roomconfig_whois' => 'moderators',
            'muc#roomconfig_publicroom' => 'true',
            //'muc#roomconfig_allowpm' => 'anyone'
        ]);

        return $query;
    }

    public static function getMembers($affiliation)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $query = $dom->createElementNS('http://jabber.org/protocol/muc#admin', 'query');

        $item = $dom->createElement('item');
        $item->setAttribute('affiliation', $affiliation);
        $query->appendChild($item);

        return $query;
    }
}
