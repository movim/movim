<?php

namespace App\Widgets\Toast;

use Movim\Widget\Base;

class Toast extends Base
{
    public function load()
    {
        $this->addjs('toast.js');
    }
}
