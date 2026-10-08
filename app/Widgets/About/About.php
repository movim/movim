<?php

namespace App\Widgets\About;

use Movim\Widget\Base;

class About extends Base
{
    public function display()
    {
        $this->view->assign('version', APP_VERSION);
    }
}
