<?php

namespace App\Widgets\PublicNavigation;

use Movim\Widget\Base;

class PublicNavigation extends Base
{
    public function load()
    {
        $this->addcss('publicnavigation.css');
    }

    public function display()
    {
        $this->view->assign('app_title', APP_TITLE);
        $this->view->assign('base_host', BASE_HOST);
    }
}
