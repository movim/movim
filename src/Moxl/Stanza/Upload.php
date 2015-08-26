<?php

namespace Moxl\Stanza;

class Upload {
    static function request($to, $name, $size, $type)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $request = $dom->createElementNS('eu:siacs:conversations:http:upload', 'request');
        $request->appendChild($dom->createElement('filename', $name));
        $request->appendChild($dom->createElement('size', $size));
        $request->appendChild($dom->createElement('content-type', $type));

        $xml = \Moxl\API::iqWrapper($request, $to, 'get');
        \Moxl\API::request($xml);
    }
}
