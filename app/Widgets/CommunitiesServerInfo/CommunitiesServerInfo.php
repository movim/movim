<?php

namespace App\Widgets\CommunitiesServerInfo;

use App\Info;
use Movim\Widget\Base;

class CommunitiesServerInfo extends Base
{
    public function display()
    {
        $this->view->assign('info', Info::where('server', $this->get('s'))
            ->where('node', '')
            ->first());
    }
}
