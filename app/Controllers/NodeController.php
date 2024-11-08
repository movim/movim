<?php

namespace App\Controllers;

use Movim\Controller\Base;

class NodeController extends Base
{
    public function dispatch()
    {
        $this->redirect('community', [$this->fetchGet('s'), $this->fetchGet('n'), $this->fetchGet('i')]);
    }
}
