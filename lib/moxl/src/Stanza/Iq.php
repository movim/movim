<?php

namespace Moxl\Stanza;

class Iq
{
    public static function error(string $to, string $id)
    {
        \Moxl\API::request(\Moxl\API::iqWrapper(false, $to, 'error', $id));
    }
}
