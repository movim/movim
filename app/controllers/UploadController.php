<?php

use Movim\Controller\Base;

class UploadController extends Base
{
    public function load()
    {
        $this->raw = true;
        $this->session_only = true;
    }
}
