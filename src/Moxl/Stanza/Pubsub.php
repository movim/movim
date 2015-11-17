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
        $xml = '
            <pubsub xmlns="http://jabber.org/protocol/pubsub">
                <create node="'.$node.'"/>
                <configure>
                    <x xmlns="jabber:x:data" type="submit">
                        <field var="FORM_TYPE" type="hidden">
                            <value>http://jabber.org/protocol/pubsub#publish-options</value>
                        </field>
                        <field var="pubsub#persist_items">
                            <value>true</value>
                        </field>
                        <field var="pubsub#access_model">
                            <value>whitelist</value>
                        </field>
                    </x>
                </configure>
            </pubsub>
            ';

        $xml = \Moxl\API::iqWrapper($xml, $to, 'set');
        \Moxl\API::request($xml);
    }
    static function configurePersistentStorage($to, $node, $access_model = 'whitelist')
    {
        $xml = '
            <pubsub xmlns="http://jabber.org/protocol/pubsub#owner">
                <configure node="'.$node.'">
                    <x xmlns="jabber:x:data" type="submit">
                        <field var="FORM_TYPE" type="hidden">
                            <value>http://jabber.org/protocol/pubsub#publish-options</value>
                        </field>
                        <field var="pubsub#persist_items">
                            <value>true</value>
                        </field>
                        <field var="pubsub#access_model">
                            <value>'.$access_model.'</value>
                        </field>
                    </x>
                </configure>
            </pubsub>
            ';

        $xml = \Moxl\API::iqWrapper($xml, $to, 'set');
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
