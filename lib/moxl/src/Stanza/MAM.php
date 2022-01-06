<?php

namespace Moxl\Stanza;

class MAM
{
    public static function getConfig()
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $prefs = $dom->createElementNS('urn:xmpp:mam:2', 'prefs');

        $xml = \Moxl\API::iqWrapper($prefs, false, 'get');
        \Moxl\API::request($xml);
    }

    public static function setConfig($default)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $prefs = $dom->createElementNS('urn:xmpp:mam:2', 'prefs');
        $prefs->setAttribute('default', $default);
        $dom->appendChild($prefs);

        $xml = \Moxl\API::iqWrapper($prefs, false, 'set');
        \Moxl\API::request($xml);
    }

    public static function get(
        $to = null,
        $id = null,
        $jid = false,
        $start = false,
        $end = false,
        $limit = false,
        $after = false,
        $before = false,
        $version = '1'
    ) {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $query = $dom->createElementNS('urn:xmpp:mam:'.$version, 'query');
        $query->setAttribute('queryid', $id);

        $x = $dom->createElement('x');
        $x->setAttribute('xmlns', 'jabber:x:data');
        $x->setAttribute('type', 'submit');
        $query->appendChild($x);

        $field_type = $dom->createElement('field');
        $field_type->setAttribute('var', 'FORM_TYPE');
        $field_type->appendChild($dom->createElement('value', 'urn:xmpp:mam:'.$version));
        $x->appendChild($field_type);

        if ($jid) {
            $field_with = $dom->createElement('field');
            $field_with->setAttribute('var', 'with');
            $field_with->appendChild($dom->createElement('value', $jid));
            $x->appendChild($field_with);
        }

        if ($start) {
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

        if ($end) {
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

        if ($limit || $after) {
            $set_limit = $dom->createElement('set');
            $set_limit->setAttribute('xmlns', 'http://jabber.org/protocol/rsm');

            if ($limit) {
                $set_limit->appendChild($dom->createElement('max', $limit));
            }

            if ($after) {
                $set_limit->appendChild($dom->createElement('after', $after));
            }

            if ($before) {
                $set_limit->appendChild(
                    ($before == true)
                        ? $dom->createElement('before')
                        : $dom->createElement('before', $before)
                );
            }

            $query->appendChild($set_limit);
        }

        $xml = \Moxl\API::iqWrapper($query, $to, 'set');
        \Moxl\API::request($xml);
    }
}
