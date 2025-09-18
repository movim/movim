<?php

namespace App\Widgets\Help;

use Movim\Widget\Base;

class Help extends Base
{
    public function ajaxAddChatroom()
    {
        $this->rpc(
            'MovimUtils.reload',
            $this->route('chat', ['movim@conference.movim.eu', 'room'])
        );
    }

    public function display()
    {
        $this->view->assign(
            'info',
            (isLogged())
                ? \App\Info::where('server', $this->me->session->host)
                         ->where('node', '')
                         ->first()
                : null
        );
    }
}
