<?php

namespace App;

class MessageOmemoHeader
{
    public $sid;
    private $keys = [];
    private $iv;
    private $payload;

    public function import($omemo)
    {
        $this->sid = $omemo->sid;
        $this->keys = $omemo->keys;
        $this->iv = $omemo->iv;
        $this->payload = $omemo->payload;
    }

    public function set($stanza)
    {
        $this->sid = (int)$stanza->encrypted->header->attributes()->sid;
        $this->iv = (string)$stanza->encrypted->header->iv;
        $this->payload = (string)$stanza->encrypted->payload;

        $keys = [];

        foreach ($stanza->encrypted->header->key as $key) {
            $keys[(string)$key->attributes()->rid] = [
                'payload' => (string)$key,
                'prekey' => (bool)$key->attributes()->prekey
            ];
        }

        $this->keys = $keys;
    }

    public function getDom()
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');

        $encrypted = $dom->createElement('encrypted');
        $encrypted->setAttribute('xmlns', 'eu.siacs.conversations.axolotl');
        $dom->appendChild($encrypted);

        $header = $dom->createElement('header');
        $header->setAttribute('sid', $this->sid);
        $encrypted->appendChild($header);

        foreach ($this->keys as $rid => $value ) {
            $key = $dom->createElement('key', $value->payload);
            $key->setAttribute('rid', $rid);

            if ($value->prekey) {
                $key->setAttribute('prekey', 'true');
            }

            $header->appendChild($key);
        }

        $iv = $dom->createElement('iv', $this->iv);
        $header->appendChild($iv);

        $payload = $dom->createElement('payload', $this->payload);
        $encrypted->appendChild($payload);

        return $dom->documentElement;
    }

    public function  __toString()
    {
        return serialize([
            'sid' => $this->sid,
            'iv' => $this->iv,
            'keys' => $this->keys,
            'payload' => $this->payload,
        ]);
    }
}