<?php
/*
 * Basic stanza for the XEP-0223 implementation
 */

namespace Moxl\Stanza;

class Storage
{
    public static $node = 'movim:configuration';
    public static $nodeConfig = [
        'FORM_TYPE' => 'http://jabber.org/protocol/pubsub#publish-options',
        'pubsub#persist_items' => 'true',
        'pubsub#access_model' => 'whitelist',
    ];

    public static function publish(array $data, bool $withPublishOption = true)
    {
        $dom = new \DOMDocument('1.0', 'utf-8');

        $pubsub = $dom->createElementNS('http://jabber.org/protocol/pubsub', 'pubsub');
        $publish = $dom->createElement('publish');
        $publish->setAttribute('node', self::$node);
        $pubsub->appendChild($publish);

        $item = $dom->createElement('item');
        $item->setAttribute('id', 'current');

        $x = $dom->createElement('x');
        $x->setAttribute('xmlns', 'jabber:x:data');
        $x->setAttribute('type', 'submit');
        $item->appendChild($x);

        $data['FORM_TYPE'] = self::$node;

        \Moxl\Utils::injectConfigInX($x, $data);

        $publish->appendChild($item);

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
