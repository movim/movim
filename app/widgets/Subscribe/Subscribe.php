<?php

use App\Configuration;
use Movim\Widget\Base;

class Subscribe extends Base
{
    public function accountNext($server)
    {
        return $this->route('accountnext', [$server]);
    }

    public function display()
    {
        $config = Configuration::get();
        $this->view->assign('config', $config);
    }
}
