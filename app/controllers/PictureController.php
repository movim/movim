<?php

use Movim\Controller\Base;

class PictureController extends Base
{
    public function load()
    {
        $this->session_only = true;
        $this->raw = true;
    }
}
