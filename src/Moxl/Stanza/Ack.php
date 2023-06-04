<?php

namespace Moxl\Stanza;

class Ack
{
    public static function send($to, $id)
    {
        \Moxl\API::request(\Moxl\API::iqWrapper(null, $to, 'result', $id));
    }
}
