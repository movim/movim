<?php

namespace App\Widgets\Snap;

use Movim\Widget\Base;

class Snap extends Base
{
    public function load()
    {
        if ($this->me->hasUpload()) {
            $this->addjs('snap.js');
            $this->addcss('snap.css');
        }
    }
}
