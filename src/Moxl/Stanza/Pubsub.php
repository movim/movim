<?php

namespace Moxl\Stanza;

use App\Post;

class Pubsub
{
    public static function create(string $node, string $name)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $pubsub = $dom->createElementNS('http://jabber.org/protocol/pubsub', 'pubsub');
        $create = $dom->createElement('create');
        $create->setAttribute('node', $node);
        $pubsub->appendChild($create);

        $configure = $dom->createElement('configure');
        $pubsub->appendChild($configure);

        $x = $dom->createElement('x');
        $x->setAttribute('xmlns', 'jabber:x:data');
        $x->setAttribute('type', 'submit');
        $configure->appendChild($x);

        \Moxl\Utils::injectConfigInX($x, [
            'FORM_TYPE' => 'http://jabber.org/protocol/pubsub#node_config',
            'pubsub#persist_items' => 'true',
            'pubsub#deliver_payloads' => 'false',
            'pubsub#send_last_published_item' => 'on_sub',
            'pubsub#access_model' => 'open',
            'pubsub#max_items' => 'max',
            'pubsub#title' => $name
        ]);

        return $pubsub;
    }

    public static function delete(string $node)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $pubsub = $dom->createElementNS('http://jabber.org/protocol/pubsub#owner', 'pubsub');
        $delete = $dom->createElement('delete');
        $delete->setAttribute('node', $node);
        $pubsub->appendChild($delete);

        return $pubsub;
    }

    public static function createCommentNode(string $node)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $pubsub = $dom->createElement('pubsub');
        $pubsub->setAttribute('xmlns', 'http://jabber.org/protocol/pubsub');

        $create = $dom->createElement('create');
        $create->setAttribute('node', Post::COMMENTS_NODE . '/' . $node);
        $pubsub->appendChild($create);

        $configure = $dom->createElement('configure');
        $pubsub->appendChild($configure);

        $x = $dom->createElement('x');
        $x->setAttribute('xmlns', 'jabber:x:data');
        $x->setAttribute('type', 'submit');
        $configure->appendChild($x);

        \Moxl\Utils::injectConfigInX($x, [
            'FORM_TYPE' => 'http://jabber.org/protocol/pubsub#node_config',
            'pubsub#persist_items' => 'true',
            'pubsub#max_items' => 'max',
            'pubsub#send_last_published_item' => 'on_sub',
            'pubsub#deliver_payloads' => 'false',
            'pubsub#access_model' => 'open',
            'pubsub#publish_model' => 'open',
            'pubsub#itemreply' => 'publisher',
            'pubsub#notify_retract' => 'true',
        ]);

        return $pubsub;
    }

    public static function subscribe($from, string $node)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $pubsub = $dom->createElementNS('http://jabber.org/protocol/pubsub', 'pubsub');
        $subscribe = $dom->createElement('subscribe');
        $subscribe->setAttribute('node', $node);
        $subscribe->setAttribute('jid', $from);
        $pubsub->appendChild($subscribe);

        return $pubsub;
    }

    public static function unsubscribe($from, string $node, $subid)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $pubsub = $dom->createElementNS('http://jabber.org/protocol/pubsub', 'pubsub');
        $unsubscribe = $dom->createElement('unsubscribe');
        $unsubscribe->setAttribute('node', $node);
        $unsubscribe->setAttribute('jid', $from);

        if (!empty($subid)) {
            $unsubscribe->setAttribute('subid', $subid);
        }

        $pubsub->appendChild($unsubscribe);

        return $pubsub;
    }

    public static function getSubscriptions(string $node)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $pubsub = $dom->createElementNS('http://jabber.org/protocol/pubsub#owner', 'pubsub');
        $subscriptions = $dom->createElement('subscriptions');
        $subscriptions->setAttribute('node', $node);
        $pubsub->appendChild($subscriptions);

        return $pubsub;
    }

    public static function getItems(
        string $node,
        int $paging = 10,
        ?string $after = null,
        ?string $before = null
    ) {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $pubsub = $dom->createElementNS('http://jabber.org/protocol/pubsub', 'pubsub');
        $items = $dom->createElement('items');
        $items->setAttribute('node', $node);

        if ($after) {
            $set = $dom->createElement('set');
            $set->setAttribute('xmlns', 'http://jabber.org/protocol/rsm');
            $set->appendChild($dom->createElement('after', $after));
            $set->appendChild($dom->createElement('max', $paging));

            $pubsub->appendChild($set);
        } elseif ($before && $before !== null && $before != 'empty') {
            $set = $dom->createElement('set');
            $set->setAttribute('xmlns', 'http://jabber.org/protocol/rsm');
            $set->appendChild($dom->createElement('before', $before));
            $set->appendChild($dom->createElement('max', $paging));

            $pubsub->appendChild($set);
        } else {
            $items->setAttribute('max_items', $paging);
        }

        $pubsub->appendChild($items);

        return $pubsub;
    }

    public static function getItem(string $node, string $id)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $pubsub = $dom->createElementNS('http://jabber.org/protocol/pubsub', 'pubsub');
        $items = $dom->createElement('items');
        $items->setAttribute('node', $node);
        $pubsub->appendChild($items);

        $item = $dom->createElement('item');
        $item->setAttribute('id', $id);
        $items->appendChild($item);

        return $pubsub;
    }

    public static function generateConfig(string $node): array
    {
        $config = [
            'FORM_TYPE' => 'http://jabber.org/protocol/pubsub#publish-options',
            'pubsub#persist_items' => 'true',
            'pubsub#max_items' => 'max',
            'pubsub#itemreply' => 'publisher',
            'pubsub#notify_sub' => 'true',
        ];

        if (in_array($node, [Post::MICROBLOG_NODE, Post::STORIES_NODE])) {
            $config['pubsub#notify_retract'] = 'true';
        }

        if ($node == Post::STORIES_NODE) {
            $config['pubsub#access_model'] = 'presence';
            $config['pubsub#item_expire'] = '86400';
        }

        return $config;
    }

    public static function postPublish(string $node, PubsubAtom $atom, bool $withPublishOption = true)
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

        if ($withPublishOption) {
            $publishOption = $dom->createElement('publish-options');
            $x = $dom->createElement('x');
            $x->setAttribute('xmlns', 'jabber:x:data');
            $x->setAttribute('type', 'submit');
            $publishOption->appendChild($x);

            \Moxl\Utils::injectConfigInX($x, self::generateConfig($node));

            $pubsub->appendChild($publishOption);
        }

        return $pubsub;
    }


    public static function testPostPublish(string $node, string $id)
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

        // Publish option
        $publishOption = $dom->createElement('publish-options');
        $x = $dom->createElement('x');
        $x->setAttribute('xmlns', 'jabber:x:data');
        $x->setAttribute('type', 'submit');
        $publishOption->appendChild($x);

        \Moxl\Utils::injectConfigInX($x, [
            'FORM_TYPE' => 'http://jabber.org/protocol/pubsub#publish-options',
            'pubsub#persist_items' => 'true',
            //'pubsub#max_items' => 'max',
            //'pubsub#send_last_published_item' => 'never',
            //'pubsub#notify_retract' => 'true',
        ]);

        $pubsub->appendChild($publishOption);

        return $pubsub;
    }

    public static function itemDelete(string $node, string $id)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $pubsub = $dom->createElementNS('http://jabber.org/protocol/pubsub', 'pubsub');
        $retract = $dom->createElement('retract');
        $retract->setAttribute('node', $node);
        $retract->setAttribute('notify', 'true');
        $pubsub->appendChild($retract);

        $item = $dom->createElement('item');
        $item->setAttribute('id', $id);
        $retract->appendChild($item);

        return $pubsub;
    }

    public static function getConfig(string $node)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $pubsub = $dom->createElementNS('http://jabber.org/protocol/pubsub#owner', 'pubsub');
        $configure = $dom->createElement('configure');
        $configure->setAttribute('node', $node);
        $pubsub->appendChild($configure);

        return $pubsub;
    }

    public static function setConfig(string $node, array $data)
    {
        $data['FORM_TYPE'] = 'http://jabber.org/protocol/pubsub#node_config';

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $pubsub = $dom->createElementNS('http://jabber.org/protocol/pubsub#owner', 'pubsub');
        $dom->appendChild($pubsub);

        $configure = $dom->createElement('configure');
        $configure->setAttribute('node', $node);
        $pubsub->appendChild($configure);

        $x = $dom->createElement('x');
        $x->setAttribute('xmlns', 'jabber:x:data');
        $x->setAttribute('type', 'submit');
        $configure->appendChild($x);

        \Moxl\Utils::injectConfigInX($x, $data);

        return $pubsub;
    }

    public static function getAffiliations(string $node)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $pubsub = $dom->createElementNS('http://jabber.org/protocol/pubsub#owner', 'pubsub');
        $affiliations = $dom->createElement('affiliations');
        $affiliations->setAttribute('node', $node);
        $pubsub->appendChild($affiliations);

        return $pubsub;
    }

    public static function setAffiliations(string $node, array $data)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $pubsub = $dom->createElementNS('http://jabber.org/protocol/pubsub#owner', 'pubsub');
        $affiliations = $dom->createElement('affiliations');
        $affiliations->setAttribute('node', $node);
        $pubsub->appendChild($affiliations);

        foreach ($data as $jid => $role) {
            $affiliation = $dom->createElement('affiliation');
            $affiliation->setAttribute('jid', $jid);
            $affiliation->setAttribute('affiliation', $role);
            $affiliations->appendChild($affiliation);
        }

        return $pubsub;
    }
}
