<?php

namespace Moxl\Stanza;

function message($to, $content)
{
    $session = \Sessionx::start();
    $xml = '
        <message xmlns="jabber:client" to="'.str_replace(' ', '\40', $to).'" type="chat" id="'.$session->id.'">
            <body>'.$content.'</body>
            <active xmlns="http://jabber.org/protocol/chatstates"/>
            <request xmlns="urn:xmpp:receipts"/>
        </message>';

    \Moxl\request($xml);
}

function messageEncrypted($to, $content)
{
    $session = \Sessionx::start();
    $xml = '
        <message xmlns="jabber:client" to="'.str_replace(' ', '\40', $to).'" type="chat" id="'.$session->id.'">
            <body>You receive an encrypted message</body>
            <x xmlns="jabber:x:encrypted">
                '.$content.'
            </x>
            <active xmlns="http://jabber.org/protocol/chatstates"/>
            <request xmlns="urn:xmpp:receipts"/>
        </message>';
    \Moxl\request($xml);
}

function messageComposing($to)
{
    $session = \Sessionx::start();
    $xml = '
        <message xmlns="jabber:client" to="'.str_replace(' ', '\40', $to).'" type="chat" id="'.$session->id.'">
            <composing xmlns="http://jabber.org/protocol/chatstates"/>
        </message>';
    \Moxl\request($xml);
}

function messagePaused($to)
{
    $session = \Sessionx::start();
    $xml = '
        <message xmlns="jabber:client" to="'.str_replace(' ', '\40', $to).'" type="chat" id="'.$session->id.'">
            <paused xmlns="http://jabber.org/protocol/chatstates"/>
        </message>';
    \Moxl\request($xml);
}
