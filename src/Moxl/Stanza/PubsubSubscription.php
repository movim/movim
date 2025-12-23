<?php

namespace Moxl\Stanza;

class PubsubSubscription
{
    private static function generateId($server, $jid, $node)
    {
        $id = '';
        $id .= $server.'<';
        $id .= $node.'<';
        $id .= $jid;

        return sha1($id);
    }

    public static function generateConfig(string $pepnode): array
    {
        return [
            'FORM_TYPE' => 'http://jabber.org/protocol/pubsub#publish-options',
            'pubsub#persist_items' => 'true',
            'pubsub#access_model' => $pepnode == 'urn:xmpp:pubsub:subscription' ? 'presence' : 'whitelist',
            'pubsub#send_last_published_item' => 'never',
            'pubsub#max_items' => 'max',
            'pubsub#notify_retract' => 'true',
        ];
    }

    public static function listAdd(string $server, string $jid, string $node, $title = null, $pepnode = 'urn:xmpp:pubsub:subscription', $withPublishOption = true)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');

        $pubsub = $dom->createElementNS('http://jabber.org/protocol/pubsub', 'pubsub');

        $publish = $dom->createElement('publish');
        $publish->setAttribute('node', $pepnode);
        $pubsub->appendChild($publish);

        $item = $dom->createElement('item');
        $item->setAttribute('id', self::generateId($server, $jid, $node));
        $publish->appendChild($item);

        $subscription = $dom->createElement('subscription');
        $subscription->setAttribute('xmlns', 'urn:xmpp:pubsub:subscription:0');
        $subscription->setAttribute('server', $server);
        $subscription->setAttribute('node', $node);
        $item->appendChild($subscription);

        if ($title) {
            $title = $dom->createElement('title', $title);
            $subscription->appendChild($title);
        }

        if ($withPublishOption) {
            $publishOption = $dom->createElement('publish-options');
            $x = $dom->createElement('x');
            $x->setAttribute('xmlns', 'jabber:x:data');
            $x->setAttribute('type', 'submit');
            $publishOption->appendChild($x);

            \Moxl\Utils::injectConfigInX($x, self::generateConfig($pepnode));

            $pubsub->appendChild($publishOption);
        }

        return $pubsub;
    }

    public static function listRemove(string $server, string $jid, string $node, $pepnode = 'urn:xmpp:pubsub:subscription')
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');

        $pubsub = $dom->createElementNS('http://jabber.org/protocol/pubsub', 'pubsub');

        $retract = $dom->createElement('retract');
        $retract->setAttribute('node', $pepnode);
        $pubsub->appendChild($retract);

        $item = $dom->createElement('item');
        $item->setAttribute('id', self::generateId($server, $jid, $node));
        $retract->appendChild($item);

        return $pubsub;
    }
}
