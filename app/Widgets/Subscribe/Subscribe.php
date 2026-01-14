<?php

namespace App\Widgets\Subscribe;

use App\Configuration;
use Movim\Widget\Base;

class Subscribe extends Base
{
    public function display()
    {
        $config = Configuration::get();
        $this->view->assign('config', $config);
    }
}
