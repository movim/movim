<?php

namespace Moxl\Stanza;

class ExtendedChannelSearch
{
    public static function search(?string $keyword = null, int $max = 30)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $search = $dom->createElement('search');
        $search->setAttribute('xmlns', 'urn:xmpp:channel-search:0:search');

        $set = $dom->createElement('set');
        $set->setAttribute('xmlns', 'http://jabber.org/protocol/rsm');
        $set->appendChild($dom->createElement('max', $max));
        $search->appendChild($set);

        $x = $dom->createElement('x');
        $x->setAttribute('xmlns', 'jabber:x:data');
        $x->setAttribute('type', 'submit');
        $search->appendChild($x);

        $field = $dom->createElement('field');
        $field->setAttribute('var', 'FORM_TYPE');
        $field->setAttribute('type', 'hidden');
        $field->appendChild($dom->createElement('value', 'urn:xmpp:channel-search:0:search-params'));
        $x->appendChild($field);

        if ($keyword != null) {
            $q = $dom->createElement('field');
            $q->setAttribute('var', 'q');
            $q->setAttribute('type', 'text-single');
            $q->appendChild($dom->createElement('value', $keyword));
            $x->appendChild($q);
        }

        return $search;
    }
}
