<?php

class CommunitiesServerInfo extends \Movim\Widget\Base
{
    function display()
    {
        $this->view->assign('info', \App\Info::where('server', $this->get('s'))
                                             ->where('node', '')
                                             ->first());
    }
}

