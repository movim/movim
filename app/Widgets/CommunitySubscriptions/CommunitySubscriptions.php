<?php

namespace App\Widgets\CommunitySubscriptions;

use App\Widgets\CommunitiesServer\CommunitiesServer;
use Movim\Widget\Base;

class CommunitySubscriptions extends Base
{
    public function load()
    {
        $this->addjs('communitysubscriptions.js');
    }

    public function ajaxHttpGet()
    {
        $view = $this->tpl();
        $view->assign('subscriptions', $this->me->subscriptions()
            ->communities()
            ->notComments()
            ->orderBy('server')->orderBy('node')
            ->get());

        $this->rpc('MovimTpl.fill', '#subscriptions', $view->draw('_communitysubscriptions'));
    }

    public function prepareTicket(\App\Info $community)
    {
        return (new CommunitiesServer($this->me, sessionId: $this->sessionId))->prepareTicket($community);
    }
}
