<?php

namespace Moxl\Xec\Payload;

use Moxl\Xec\Action\Version\Send;

class Version extends Payload
{
    public function handle($stanza, $parent = false)
    {
        $to = (string)$parent->attributes()->from;
        $id = (string)$parent->attributes()->id;

        $vd = new Send;
        $vd->setTo($to)
           ->setId($id)
           ->setName(ucfirst(APP_NAME))
           ->setVersion(APP_VERSION)
           ->setOs(PHP_OS)
           ->request();
    }
}
