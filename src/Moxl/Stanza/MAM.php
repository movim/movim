<?php

namespace Moxl\Stanza;

class MAM {
    static function get($jid = false, $start = false, $end = false, $limit = false)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $query = $dom->createElementNS('urn:xmpp:mam:0', 'query');
        $x = $dom->createElement('x');
        $x->setAttribute('xmlns', 'jabber:x:data');
        $x->setAttribute('type', 'submit');
        $query->appendChild($x);

        $field_type = $dom->createElement('field');
        $field_type->setAttribute('var', 'FORM_TYPE');
        $field_type->appendChild($dom->createElement('value', 'urn:xmpp:mam:0'));
        $x->appendChild($field_type);

        if($jid) {
            $field_with = $dom->createElement('field');
            $field_with->setAttribute('var', 'with');
            $field_with->appendChild($dom->createElement('value', $jid));
            $x->appendChild($field_with);
        }

        if($start) {
            $field_start = $dom->createElement('field');
            $field_start->setAttribute('var', 'start');
            $field_start->appendChild(
                $dom->createElement(
                    'value',
                    date('Y-m-d\TH:i:s\Z', $start+1)
                )
            );
            $x->appendChild($field_start);
        }

        if($end) {
            $field_end = $dom->createElement('field');
            $field_end->setAttribute('var', 'end');
            $field_end->appendChild(
                $dom->createElement(
                    'value',
                    date('Y-m-d\TH:i:s\Z', $end+1)
                )
            );
            $x->appendChild($field_end);
        }

        if($limit) {
            $field_limit = $dom->createElement('field');
            $field_limit->setAttribute('var', 'limit');
            $field_limit->appendChild($dom->createElement('value', $limit));
            $x->appendChild($field_limit);
        }

        $xml = \Moxl\API::iqWrapper($query, null, 'set');
        \Moxl\API::request($xml);
    }
}
