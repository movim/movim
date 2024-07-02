<?php

namespace App\Widgets\About;

class About extends \Movim\Widget\Base
{
    public function display()
    {
        $this->view->assign('version', APP_VERSION);
    }
}
