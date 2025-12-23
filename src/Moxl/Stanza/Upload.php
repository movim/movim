<?php

namespace Moxl\Stanza;

class Upload
{
    public static function request($name, $size, $type)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $request = $dom->createElementNS('urn:xmpp:http:upload:0', 'request');
        $request->setAttribute('filename', $name);
        $request->setAttribute('size', $size);
        $request->setAttribute('content-type', $type);

        return $request;
    }
}
