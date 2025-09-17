<?php

namespace App\Widgets\CommunitySubscriptions;

use App\Widgets\CommunitiesServer\CommunitiesServer;
use Movim\Widget\Base;

class CommunitySubscriptions extends Base
{
    private $_list_server;

    public function load()
    {
        $this->addjs('communitysubscriptions.js');
    }

    public function checkNewServer($node)
    {
        $r = ($this->_list_server != $node->server);
        $this->_list_server = $node->server;
        return $r;
    }

    public function ajaxHttpGet()
    {
        $view = $this->tpl();
        $view->assign('subscriptions', $this->me->subscriptions()
            ->notComments()
            ->orderBy('server')->orderBy('node')
            ->get());

        $this->rpc('MovimTpl.fill', '#subscriptions', $view->draw('_communitysubscriptions'));
    }

    public function prepareTicket(\App\Info $community)
    {
        return (new CommunitiesServer())->prepareTicket($community);
    }
}
