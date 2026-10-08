<?php

namespace Moxl\Stanza;

use App\Conference;
use Illuminate\Support\Collection;
use Moxl\Utils;

class Bookmark2
{
    public const VERSION = '1';

    public const NODE = 'urn:xmpp:bookmarks:';

    public const HIERARCHY_NAMESPACE = 'https://slidge.im/spaces/bookmarks-hierarchy';

    public const NODE_CONFIG = [
        'FORM_TYPE' => 'http://jabber.org/protocol/pubsub#publish-options',
        'pubsub#persist_items' => 'true',
        'pubsub#access_model' => 'whitelist',
        'pubsub#send_last_published_item' => 'never',
        'pubsub#max_items' => 'max',
        'pubsub#notify_retract' => 'true',
    ];

    public static function get(?string $version = self::VERSION)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $pubsub = $dom->createElementNS('http://jabber.org/protocol/pubsub', 'pubsub');

        $items = $dom->createElement('items');
        $items->setAttribute('node', self::NODE.$version);
        $pubsub->appendChild($items);

        return $pubsub;
    }

    public static function setDirectories(
        Collection $directories,
        ?string $version = self::VERSION,
        ?string $node = null,
        ?bool $withPublishOption = true,
        ?array $nodeConfig = self::NODE_CONFIG
    ) {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $pubsub = $dom->createElementNS('http://jabber.org/protocol/pubsub', 'pubsub');

        $publish = $dom->createElement('publish');
        $publish->setAttribute('node', $node == null
            ? self::NODE.$version
            : $node);
        $pubsub->appendChild($publish);

        $item = $dom->createElement('item');
        $item->setAttribute('id', self::HIERARCHY_NAMESPACE);
        $publish->appendChild($item);

        $directoriesNode = $dom->createElement('directories');
        $directoriesNode->setAttribute('xmlns', self::HIERARCHY_NAMESPACE);
        $item->appendChild($directoriesNode);

        foreach ($directories as $id => $title) {
            $directory = $dom->createElement('directory');
            $directory->setAttribute('id', $id);
            $directory->setAttribute('title', $title);
            $directoriesNode->appendChild($directory);
        }

        if ($withPublishOption) {
            $publishOption = $dom->createElement('publish-options');
            $x = $dom->createElement('x');
            $x->setAttribute('xmlns', 'jabber:x:data');
            $x->setAttribute('type', 'submit');
            $publishOption->appendChild($x);

            Utils::injectConfigInX($x, $nodeConfig);

            $pubsub->appendChild($publishOption);
        }

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
            ? self::NODE.$version
            : $node);
        $pubsub->appendChild($publish);

        $item = $dom->createElement('item');
        $item->setAttribute('id', $configuration->conference);
        $publish->appendChild($item);

        $conference = $dom->createElement('conference');
        $conference->setAttribute('xmlns', self::NODE.$version);
        $conference->setAttribute('name', $configuration->name);
        if ($configuration->autojoin) {
            $conference->setAttribute('autojoin', 'true');
        }
        $item->appendChild($conference);

        if ($configuration->nick) {
            $nick = $dom->createElement('nick', $configuration->nick);
            $conference->appendChild($nick);
        }

        $extensions = $dom->createElement('extensions');
        $conference->appendChild($extensions);

        if ($configuration->extensions) {
            $domExtensions = new \DOMDocument('1.0', 'UTF-8');
            $domExtensions->loadXML($configuration->extensions);

            foreach ($domExtensions->documentElement->childNodes as $child) {
                $extensions->appendChild($dom->importNode($child, true));
            }
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

        if ($configuration->weight !== null) {
            $hierarchy = $dom->getElementsByTagName('hierarchy');

            if ($hierarchy->length > 0) {
                $hierarchy->item(0)->remove();
            }

            $hierarchy = $dom->createElement('hierarchy');
            $hierarchy->setAttribute('xmlns', Bookmark2::HIERARCHY_NAMESPACE);
            $hierarchy->setAttribute('weight', $configuration->weight);

            if ($configuration->directory_id) {
                $hierarchy->setAttribute('directory-id', $configuration->directory_id);
            }

            $extensions->appendChild($hierarchy);
        }

        if ($configuration->call == true) {
            $conferenceCall = $dom->createElement('conference-call');
            $conferenceCall->setAttribute('xmlns', Conference::XMLNS_MOVIM_CONFERENCE_CALL);
            $extensions->appendChild($conferenceCall);
        }

        if ($withPublishOption) {
            $publishOption = $dom->createElement('publish-options');
            $x = $dom->createElement('x');
            $x->setAttribute('xmlns', 'jabber:x:data');
            $x->setAttribute('type', 'submit');
            $publishOption->appendChild($x);

            Utils::injectConfigInX($x, $nodeConfig);

            $pubsub->appendChild($publishOption);
        }

        return $pubsub;
    }
}
