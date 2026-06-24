<?php

namespace App\Controllers;

use Movim\Controller\Base;

class PictureController extends Base
{
    public function load()
    {
        $this->session_only = ($this->fetchGet('type') != 'avatar');
        $this->set_cookie = false;
        $this->raw = true;
    }
}
