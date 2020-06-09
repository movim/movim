<?php

use Movim\Widget\Base;

class Help extends Base
{
    public function ajaxAddChatroom()
    {
        $this->rpc(
            'MovimUtils.redirect',
            $this->route('chat', ['movim@conference.movim.eu', 'room'])
        );
    }

    public function display()
    {
        $this->view->assign(
            'info',
            (isLogged())
                ? \App\Info::where('server', $this->user->session->host)
                         ->where('node', '')
                         ->first()
                : null
        );
    }
}
