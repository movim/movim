<?php

class Help extends \Movim\Widget\Base
{
    function ajaxAddChatroom()
    {
        $this->rpc(
            'MovimUtils.redirect',
            $this->route('chat', ['movim@conference.movim.eu', 'room'])
        );
    }

    function display()
    {
        $this->view->assign('info', \App\Info::where('server', $this->user->getServer())
                                             ->where('node', '')
                                             ->first());
    }
}

