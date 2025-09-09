<?php

namespace Moxl\Stanza;

use App\Conference;

class Bookmark2
{
    public static $node = 'urn:xmpp:bookmarks:';
    public static $nodeConfig = [
        'FORM_TYPE' => 'http://jabber.org/protocol/pubsub#publish-options',
        'pubsub#persist_items' => 'true',
        'pubsub#access_model' => 'whitelist',
        'pubsub#send_last_published_item' => 'never',
        'pubsub#max_items' => 'max',
        'pubsub#notify_retract' => 'true',
    ];

    public static function get($version = '1')
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $pubsub = $dom->createElementNS('http://jabber.org/protocol/pubsub', 'pubsub');

        $items = $dom->createElement('items');
        $items->setAttribute('node', self::$node . $version);
        $pubsub->appendChild($items);

        \Moxl\API::request(\Moxl\API::iqWrapper($pubsub, false, 'get'));
    }

    public static function set(Conference $configuration, $version = '1', bool $withPublishOption = true)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $pubsub = $dom->createElementNS('http://jabber.org/protocol/pubsub', 'pubsub');

        $publish = $dom->createElement('publish');
        $publish->setAttribute('node', self::$node . $version);
        $pubsub->appendChild($publish);

        $item = $dom->createElement('item');
        $item->setAttribute('id', $configuration->conference);
        $publish->appendChild($item);

        $conference = $dom->createElement('conference');
        $conference->setAttribute('xmlns', self::$node . $version);
        $conference->setAttribute('name', $configuration->name);
        if ($configuration->autojoin) {
            $conference->setAttribute('autojoin', 'true');
        }
        $item->appendChild($conference);

        $nick = $dom->createElement('nick', $configuration->nick);
        $conference->appendChild($nick);

        if ($configuration->extensions) {
            $domExtensions = new \DOMDocument('1.0', 'UTF-8');
            $domExtensions->loadXML($configuration->extensions);

            $extensions = $dom->importNode($domExtensions->documentElement, true);
            $conference->appendChild($extensions);
        } else if ($configuration->notify !== null) {
            $extensions = $dom->createElement('extensions');
            $conference->appendChild($extensions);
        }

        if ($configuration->notify !== null) {
            $notify = $dom->createElement('notify');
            $notify->setAttribute('xmlns', Conference::$xmlnsNotifications);
            $notify->appendChild($dom->createElement($configuration->notificationKey));
            $extensions->appendChild($notify);
        }

        if ($configuration->pinned == true) {
            $pinned = $dom->createElement('pinned');
            $pinned->setAttribute('xmlns', Conference::$xmlnsPinned);
            $extensions->appendChild($pinned);
        }

        if ($withPublishOption) {
            $publishOption = $dom->createElement('publish-options');
            $x = $dom->createElement('x');
            $x->setAttribute('xmlns', 'jabber:x:data');
            $x->setAttribute('type', 'submit');
            $publishOption->appendChild($x);

            \Moxl\Utils::injectConfigInX($x, self::$nodeConfig);

            $pubsub->appendChild($publishOption);
        }

        \Moxl\API::request(\Moxl\API::iqWrapper($pubsub, false, 'set'));
    }
}
