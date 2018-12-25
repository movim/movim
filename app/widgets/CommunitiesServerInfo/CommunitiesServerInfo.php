<?php

use Movim\Widget\Base;

class CommunitiesServerInfo extends Base
{
    public function display()
    {
        $this->view->assign('info', \App\Info::where('server', $this->get('s'))
                                             ->where('node', '')
                                             ->first());
    }
}
