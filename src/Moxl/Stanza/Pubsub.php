<?php

namespace Moxl\Stanza;

class Pubsub {
    static function create($to, $node)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $pubsub = $dom->createElementNS('http://jabber.org/protocol/pubsub', 'pubsub');
        $create = $dom->createElement('create');
        $create->setAttribute('node', $node);
        $pubsub->appendChild($create);

        $xml = \Moxl\API::iqWrapper($pubsub, $to, 'set');
        \Moxl\API::request($xml);
    }

    static function delete($to, $node)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $pubsub = $dom->createElementNS('http://jabber.org/protocol/pubsub#owner', 'pubsub');
        $delete = $dom->createElement('delete');
        $delete->setAttribute('node', $node);
        $pubsub->appendChild($delete);

        $xml = \Moxl\API::iqWrapper($pubsub, $to, 'set');
        \Moxl\API::request($xml);
    }

    static function createPersistentStorage($to, $node)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $pubsub = $dom->createElement('pubsub');
        $pubsub->setAttribute('xmlns', 'http://jabber.org/protocol/pubsub');
        $create = $dom->createElement('create');
        $create->setAttribute('node', $node);
        $pubsub->appendChild($create);

        $configure = $dom->createElement('configure');
        $pubsub->appendChild($configure);

        $x = $dom->createElement('x');
        $x->setAttribute('jabber:x:data');
        $x->setAttribute('type', 'submit');
        $configure->appendChild($x);

        $field = $dom->createElement('field');
        $field->setAttribute('var', 'FORM_TYPE');
        $field->setAttribute('type', 'hidden');
        $x->appendChild($field);

        $value = $dom->createElement('value', 'http://jabber.org/protocol/pubsub#node_config');
        $field->appendChild($value);

        $field = $dom->createElement('field');
        $field->setAttribute('var', 'pubsub#persist_items');
        $x->appendChild($field);

        $value = $dom->createElement('value', 'true');
        $field->appendChild($value);

        $field = $dom->createElement('field');
        $field->setAttribute('var', 'pubsub#access_model');
        $x->appendChild($field);

        $value = $dom->createElement('value', 'whitelist');
        $field->appendChild($value);

        $xml = \Moxl\API::iqWrapper($pubsub, $to, 'set');
        \Moxl\API::request($xml);
    }
    static function configurePersistentStorage($to, $node, $access_model = 'whitelist')
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $pubsub = $dom->createElement('pubsub');
        $pubsub->setAttribute('xmlns', 'http://jabber.org/protocol/pubsub');
        $create = $dom->createElement('create');
        $create->setAttribute('node', $node);
        $pubsub->appendChild($create);

        $configure = $dom->createElement('configure');
        $pubsub->appendChild($configure);

        $x = $dom->createElement('x');
        $x->setAttribute('jabber:x:data');
        $x->setAttribute('type', 'submit');
        $configure->appendChild($x);

        $field = $dom->createElement('field');
        $field->setAttribute('var', 'FORM_TYPE');
        $field->setAttribute('type', 'hidden');
        $x->appendChild($field);

        $value = $dom->createElement('value', 'http://jabber.org/protocol/pubsub#node_config');
        $field->appendChild($value);

        $field = $dom->createElement('field');
        $field->setAttribute('var', 'pubsub#persist_items');
        $x->appendChild($field);

        $value = $dom->createElement('value', 'true');
        $field->appendChild($value);

        if(empty($access_model)) $access_model = 'whitelist';

        $field = $dom->createElement('field');
        $field->setAttribute('var', 'pubsub#access_model');
        $x->appendChild($field);

        $value = $dom->createElement('value', $access_model);
        $field->appendChild($value);

        $xml = \Moxl\API::iqWrapper($pubsub, $to, 'set');
        \Moxl\API::request($xml);
    }

    static function subscribe($to, $from, $node)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $pubsub = $dom->createElementNS('http://jabber.org/protocol/pubsub', 'pubsub');
        $subscribe = $dom->createElement('subscribe');
        $subscribe->setAttribute('node', $node);
        $subscribe->setAttribute('jid', $from);
        $pubsub->appendChild($subscribe);

        $xml = \Moxl\API::iqWrapper($pubsub, $to, 'set');
        \Moxl\API::request($xml);
    }

    static function unsubscribe($to, $from, $node, $subid)
    {
        if($subid != '')
            $subid = 'subid="'.$subid.'"';
        $xml = '
            <pubsub xmlns="http://jabber.org/protocol/pubsub">
                <unsubscribe
                    node="'.$node.'"
                    jid="'.$from.'"
                    '.$subid.'/>
            </pubsub>';

        $xml = \Moxl\API::iqWrapper($xml, $to, 'set');

        \Moxl\API::request($xml);
    }

    static function getSubscriptions($to, $node)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $pubsub = $dom->createElementNS('http://jabber.org/protocol/pubsub#owner', 'pubsub');
        $subscriptions = $dom->createElement('subscriptions');
        $subscriptions->setAttribute('node', $node);
        $pubsub->appendChild($subscriptions);

        $xml = \Moxl\API::iqWrapper($pubsub, $to, 'get');
        \Moxl\API::request($xml);
    }

    static function setSubscriptions($to, $node, $data)
    {
        $subscriptions = "";
        foreach($data as $jid_subid => $subscription){
            list($jid, $subid) = split("_", $jid_subid);
            $subscriptions .= '
                <subscription
                    jid="'.$jid.'" ';
                if($subid != null)
                    $subscriptions .=
                        'subid="'.$subid.'" ';
                $subscriptions .= '
                    subscription="'.$subscription.'" />';
        }

        $xml = '
            <pubsub xmlns="http://jabber.org/protocol/pubsub#owner">
                <subscriptions node="'.$node.'">
                '.$subscriptions.'
                </subscriptions>
            </pubsub>';

        $xml = \Moxl\API::iqWrapper($xml, $to, 'set');
        \Moxl\API::request($xml);
    }

    static function getItems($to, $node)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $pubsub = $dom->createElementNS('http://jabber.org/protocol/pubsub', 'pubsub');
        $items = $dom->createElement('items');
        $items->setAttribute('node', $node);
        $items->setAttribute('max_items', 40);
        $pubsub->appendChild($items);

        $xml = \Moxl\API::iqWrapper($pubsub, $to, 'get');
        \Moxl\API::request($xml);
    }

    static function getItem($to, $node, $id)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $pubsub = $dom->createElementNS('http://jabber.org/protocol/pubsub', 'pubsub');
        $items = $dom->createElement('items');
        $items->setAttribute('node', $node);
        $pubsub->appendChild($items);

        $item = $dom->createElement('item');
        $item->setAttribute('id', $id);
        $items->appendChild($item);

        $xml = \Moxl\API::iqWrapper($pubsub, $to, 'get');
        \Moxl\API::request($xml);
    }

    static function postPublish($to, $node, $atom)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');

        $atomxml = $dom->importNode($atom->getDom(), true);

        $pubsub = $dom->createElementNS('http://jabber.org/protocol/pubsub', 'pubsub');
        $publish = $dom->createElement('publish');
        $publish->setAttribute('node', $node);
        $pubsub->appendChild($publish);

        $item = $dom->createElement('item');
        $item->appendChild($atomxml);
        $item->setAttribute('id', $atom->id);
        $publish->appendChild($item);

        $xml = \Moxl\API::iqWrapper($pubsub, $to, 'set');

        \Moxl\API::request($xml);
    }


    static function testPostPublish($to, $node, $id)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $pubsub = $dom->createElementNS('http://jabber.org/protocol/pubsub', 'pubsub');
        $publish = $dom->createElement('publish');
        $publish->setAttribute('node', $node);
        $pubsub->appendChild($publish);

        $item = $dom->createElement('item');
        $item->setAttribute('id', $id);
        $publish->appendChild($item);

        $entry = $dom->createElementNS('http://www.w3.org/2005/Atom', 'entry');
        $item->appendChild($entry);

        $xml = \Moxl\API::iqWrapper($pubsub, $to, 'set');
        \Moxl\API::request($xml);
    }

    static function postDelete($to, $node, $id)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $pubsub = $dom->createElementNS('http://jabber.org/protocol/pubsub', 'pubsub');
        $retract = $dom->createElement('retract');
        $retract->setAttribute('node', $node);
        $retract->setAttribute('notify', true);
        $pubsub->appendChild($retract);

        $item = $dom->createElement('item');
        $item->setAttribute('id', $id);
        $retract->appendChild($item);

        $xml = \Moxl\API::iqWrapper($pubsub, $to, 'set');
        \Moxl\API::request($xml);
    }

    static function getConfig($to, $node)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $pubsub = $dom->createElementNS('http://jabber.org/protocol/pubsub#owner', 'pubsub');
        $configure = $dom->createElement('configure');
        $configure->setAttribute('node', $node);
        $pubsub->appendChild($configure);

        $xml = \Moxl\API::iqWrapper($pubsub, $to, 'get');
        \Moxl\API::request($xml);
    }

    static function setConfig($to, $node, $data)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $pubsub = $dom->createElementNS('http://jabber.org/protocol/pubsub#owner', 'pubsub');
        $dom->appendChild($pubsub);

        $configure = $dom->createElement('configure');
        $configure->setAttribute('node', $node);
        $pubsub->appendChild($configure);

        $x = $dom->createElementNS('jabber:x:data', 'x');
        $x->setAttribute('type', 'submit');
        $configure->appendChild($x);

        $xmpp = new \FormtoXMPP($data);
        $xmpp->create();
        $xmpp->appendToX($dom);

        $xml = \Moxl\API::iqWrapper($pubsub, $to, 'set');
        \Moxl\API::request($xml);
    }

    static function getAffiliations($to, $node)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $pubsub = $dom->createElementNS('http://jabber.org/protocol/pubsub#owner', 'pubsub');
        $affiliations = $dom->createElement('affiliations');
        $affiliations->setAttribute('node', $node);
        $pubsub->appendChild($affiliations);

        $xml = \Moxl\API::iqWrapper($pubsub, $to, 'get');
        \Moxl\API::request($xml);
    }

    static function setAffiliations($to, $node, $data)
    {
        $affiliations = "";
        foreach($data as $jid_subid => $affiliation){
            $split = split("_", $jid_subid);
            $affiliations .= '
                <affiliation
                    jid="'.$split[0].'"
                    subid="'.$split[1].'"
                    affiliation="'.$affiliation.'" />';
        }

        $xml = '<pubsub xmlns="http://jabber.org/protocol/pubsub#owner">
                <affiliations node="'.$node.'">
                '.$affiliations.'
                </affiliations>
            </pubsub>';

        $xml = \Moxl\API::iqWrapper($xml, $to, 'set');
        \Moxl\API::request($xml);
    }
}
