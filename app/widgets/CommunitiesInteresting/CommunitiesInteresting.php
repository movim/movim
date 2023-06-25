<?php

use Movim\Widget\Base;

include_once WIDGETS_PATH . 'CommunitiesServer/CommunitiesServer.php';

class CommunitiesInteresting extends Base
{
    public function display()
    {
        $this->view->assign('communities', $this->user->session
            ->interestingCommunities(6)
            ->inRandomOrder()
            ->get()
        );
    }

    public function prepareTicket(\App\Info $community)
    {
        return (new CommunitiesServer)->prepareTicket($community);
    }
}
