<?php

namespace Moxl\Stanza;

class Space
{
    public const NODE_CONFIG = [
        'pubsub#type' => 'urn:xmpp:spaces:0',
        'pubsub#notify_retract' => 'true',
        'pubsub#persist_items' => 'true',
        'pubsub#purge_offline' => 'false',
        'pubsub#notify_sub' => 'true',
        'pubsub#notify_config' => 'true',
        'pubsub#notify_delete' => 'true',
        'pubsub#max_items' => 'max',
        'pubsub#publish_model' => 'publishers',
        'pubsub#access_model' => 'authorize',
        'pubsub#deliver_payloads' => 'true',
    ];

    public static function create(string $node, string $title)
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
        $x->setAttribute('xmlns', 'jabber:x:data');
        $x->setAttribute('type', 'submit');
        $configure->appendChild($x);

        \Moxl\Utils::injectConfigInX($x, array_merge([
            'FORM_TYPE' => 'http://jabber.org/protocol/pubsub#node_config',
            'pubsub#title' => $title,
        ], self::NODE_CONFIG));

        return $pubsub;
    }

    public static function setSubscription(string $node, string $jid, string $state)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $pubsub = $dom->createElement('pubsub');
        $pubsub->setAttribute('xmlns', 'http://jabber.org/protocol/pubsub#owner');

        $subscriptions = $dom->createElement('subscriptions');
        $subscriptions->setAttribute('node', $node);
        $pubsub->appendChild($subscriptions);

        $subscription = $dom->createElement('subscription');
        $subscription->setAttribute('jid', $jid);
        $subscription->setAttribute('subscription', $state);
        $subscriptions->appendChild($subscription);

        return $pubsub;
    }
}
