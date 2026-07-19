<?php

namespace Movim;

class Jid
{
    public ?string $jid;
    public ?string $domain;
    public ?string $username;
    public ?string $resource;

    public function __construct(string $jid)
    {
        if (validateJid($jid)) {
            $this->jid = $jid;

            $arr = explode('/', $jid);
            $jid = $arr[0];

            $this->resource = count($arr) > 1 ? implode('/', array_slice($arr, 1)) : null;
            $this->username = null;

            $arr = explode('@', $jid);

            $this->domain = $arr[0];
            if (isset($arr[1])) {
                $this->username = $arr[0];
                $this->domain = $arr[1];
            }
        }
    }

    public function __toString()
    {
        return $this->jid;
    }

    public function bareJid(): string
    {
        return $this->username . '@' . $this->domain;
    }
}
