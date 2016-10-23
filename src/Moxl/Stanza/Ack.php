<?php

namespace Moxl\Stanza;

class Ack {
    static function send($to, $id)
    {
        \Moxl\API::request(\Moxl\API::iqWrapper(false, $to, 'result', $id));
    }
}
