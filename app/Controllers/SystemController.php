<?php

namespace App\Controllers;
use Movim\Controller\Base;

class SystemController extends Base
{
    public function load()
    {
        $this->raw = true;
    }
}
