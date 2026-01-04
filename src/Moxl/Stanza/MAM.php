<?php

namespace Moxl\Stanza;

class MAM
{
    public static function getConfig()
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $prefs = $dom->createElementNS('urn:xmpp:mam:2', 'prefs');

        return $prefs;
    }

    public static function setConfig($default)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $prefs = $dom->createElementNS('urn:xmpp:mam:2', 'prefs');
        $prefs->setAttribute('default', $default);
        $dom->appendChild($prefs);

        return $prefs;
    }

    public static function get(
        ?string $id = null,
        ?string $jid = null,
        ?int $start = null,
        ?int $end = null,
        ?int $limit = null,
        ?string $after = null,
        ?string $before = null,
        string $version = '1'
    ) {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $query = $dom->createElementNS('urn:xmpp:mam:' . $version, 'query');
        $query->setAttribute('queryid', $id);

        $x = $dom->createElement('x');
        $x->setAttribute('xmlns', 'jabber:x:data');
        $x->setAttribute('type', 'submit');
        $query->appendChild($x);

        $fieldType = $dom->createElement('field');
        $fieldType->setAttribute('var', 'FORM_TYPE');
        $fieldType->appendChild($dom->createElement('value', 'urn:xmpp:mam:' . $version));
        $x->appendChild($fieldType);

        if ($jid !== null) {
            $fieldWith = $dom->createElement('field');
            $fieldWith->setAttribute('var', 'with');
            $fieldWith->appendChild($dom->createElement('value', $jid));
            $x->appendChild($fieldWith);
        }

        if ($start) {
            $fieldStart = $dom->createElement('field');
            $fieldStart->setAttribute('var', 'start');
            $fieldStart->appendChild(
                $dom->createElement(
                    'value',
                    date('Y-m-d\TH:i:s\Z', $start/*+1*/)
                )
            );
            $x->appendChild($fieldStart);
        }

        if ($end) {
            $fieldEnd = $dom->createElement('field');
            $fieldEnd->setAttribute('var', 'end');
            $fieldEnd->appendChild(
                $dom->createElement(
                    'value',
                    date('Y-m-d\TH:i:s\Z', $end/*+1*/)
                )
            );
            $x->appendChild($fieldEnd);
        }

        if ($limit !== null || $after !== null || $before !== null) {
            $setLimit = $dom->createElement('set');
            $setLimit->setAttribute('xmlns', 'http://jabber.org/protocol/rsm');

            if ($limit !== null) {
                $setLimit->appendChild($dom->createElement('max', $limit));
            }

            if ($after !== null) {
                $setLimit->appendChild($dom->createElement('after', $after));
            }

            if ($before !== null) {
                $setLimit->appendChild(
                    ($before === '')
                        ? $dom->createElement('before')
                        : $dom->createElement('before', $before)
                );
            }

            $query->appendChild($setLimit);
        }

        return $query;
    }
}
