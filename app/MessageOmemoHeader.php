<?php

namespace App;

class MessageOmemoHeader
{
    private $sid;
    private $keys = [];
    private $iv;
    private $payload;

    public function set($stanza)
    {
        $this->sid = (string)$stanza->encrypted->header->attributes()->sid;
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