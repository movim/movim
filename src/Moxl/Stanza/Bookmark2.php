<?php

namespace Moxl\Stanza;

use App\Conference;

class Bookmark2
{
    public const VERSION = '1';
    public const NODE = 'urn:xmpp:bookmarks:';
    public const NODE_CONFIG = [
        'FORM_TYPE' => 'http://jabber.org/protocol/pubsub#publish-options',
        'pubsub#persist_items' => 'true',
        'pubsub#access_model' => 'whitelist',
        'pubsub#send_last_published_item' => 'never',
        'pubsub#max_items' => 'max',
        'pubsub#notify_retract' => 'true',
    ];

    public static function get($version = self::VERSION)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $pubsub = $dom->createElementNS('http://jabber.org/protocol/pubsub', 'pubsub');

        $items = $dom->createElement('items');
        $items->setAttribute('node', self::NODE . $version);
        $pubsub->appendChild($items);

        return $pubsub;
    }

    public static function set(
        Conference $configuration,
        ?string $version = self::VERSION,
        ?string $node = null,
        ?bool $withPublishOption = true,
        ?array $nodeConfig = self::NODE_CONFIG
    ) {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $pubsub = $dom->createElementNS('http://jabber.org/protocol/pubsub', 'pubsub');

        $publish = $dom->createElement('publish');
        $publish->setAttribute('node', $node == null
            ? self::NODE . $version
            : $node);
        $pubsub->appendChild($publish);

        $item = $dom->createElement('item');
        $item->setAttribute('id', $configuration->conference);
        $publish->appendChild($item);

        $conference = $dom->createElement('conference');
        $conference->setAttribute('xmlns', self::NODE . $version);
        $conference->setAttribute('name', $configuration->name);
        if ($configuration->autojoin) {
            $conference->setAttribute('autojoin', 'true');
        }
        $item->appendChild($conference);

        if ($configuration->nick) {
            $nick = $dom->createElement('nick', $configuration->nick);
            $conference->appendChild($nick);
        }

        if ($configuration->extensions) {
            $domExtensions = new \DOMDocument('1.0', 'UTF-8');
            $domExtensions->loadXML($configuration->extensions);

            $extensions = $dom->importNode($domExtensions->documentElement, true);
            $conference->appendChild($extensions);
        } else if ($configuration->notify !== null || $configuration->pinned == true) {
            $extensions = $dom->createElement('extensions');
            $conference->appendChild($extensions);
        }

        if ($configuration->notify !== null) {
            $notify = $dom->createElement('notify');
            $notify->setAttribute('xmlns', Conference::XMLNS_NOTIFICATIONS);
            $notify->appendChild($dom->createElement($configuration->notificationKey));
            $extensions->appendChild($notify);
        }

        if ($configuration->pinned == true) {
            $pinned = $dom->createElement('pinned');
            $pinned->setAttribute('xmlns', Conference::XMLNS_PINNED);
            $extensions->appendChild($pinned);
        }

        if ($withPublishOption) {
            $publishOption = $dom->createElement('publish-options');
            $x = $dom->createElement('x');
            $x->setAttribute('xmlns', 'jabber:x:data');
            $x->setAttribute('type', 'submit');
            $publishOption->appendChild($x);

            \Moxl\Utils::injectConfigInX($x, $nodeConfig);

            $pubsub->appendChild($publishOption);
        }

        return $pubsub;
    }
}
