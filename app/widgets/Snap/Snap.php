<?php

use Movim\Widget\Base;

class Snap extends Base
{
    public function load()
    {
        $this->addjs('snap.js');
        $this->addcss('snap.css');
    }
}
