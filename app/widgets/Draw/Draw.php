<?php

use Movim\Widget\Base;

class Draw extends Base
{
    public function load()
    {
        $this->addjs('draw.js');
        $this->addcss('draw.css');
    }
}
