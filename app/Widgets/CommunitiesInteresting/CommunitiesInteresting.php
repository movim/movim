<?php

namespace App\Widgets\CommunitiesInteresting;

use App\Widgets\CommunitiesServer\CommunitiesServer;
use Movim\Widget\Base;

class CommunitiesInteresting extends Base
{
    public function display()
    {
        $this->view->assign(
            'communities',
            $this->me->session
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
