<?php

use Movim\Controller\Base;

class ManifestController extends Base
{
    public function load()
    {
        header('Content-type: application/json');
        $this->raw = true;
    }
}
