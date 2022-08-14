<?php

namespace Moxl\Stanza;

class Muclumbus
{
    public static function search($keyword)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $search = $dom->createElement('search');
        $search->setAttribute('xmlns', 'https://xmlns.zombofant.net/muclumbus/search/1.0');

        $set = $dom->createElement('set');
        $set->setAttribute('xmlns', 'http://jabber.org/protocol/rsm');
        $set->appendChild($dom->createElement('max', 30));
        $search->appendChild($set);

        $x = $dom->createElement('x');
        $x->setAttribute('xmlns', 'jabber:x:data');
        $x->setAttribute('type', 'submit');
        $search->appendChild($x);

        $field = $dom->createElement('field');
        $field->setAttribute('var', 'FORM_TYPE');
        $field->setAttribute('type', 'hidden');
        $field->appendChild($dom->createElement('value', 'https://xmlns.zombofant.net/muclumbus/search/1.0#params'));
        $x->appendChild($field);

        if (!empty($keyword)) {
            $q = $dom->createElement('field');
            $q->setAttribute('var', 'q');
            $q->setAttribute('type', 'text-single');
            $q->appendChild($dom->createElement('value', $keyword));
            $x->appendChild($q);
        }

        $xml = \Moxl\API::iqWrapper($search, 'rodrigo.de.mucobedo@dreckshal.de', 'get');
        \Moxl\API::request($xml);
    }
}
