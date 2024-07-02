<?php

namespace App\Controllers;

use Movim\Controller\Base;

class FeedController extends Base
{
    public function load()
    {
        $this->raw = true;
    }
}
