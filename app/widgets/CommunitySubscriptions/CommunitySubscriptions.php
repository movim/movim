<?php

use Respect\Validation\Validator;

class CommunitySubscriptions extends \Movim\Widget\Base
{
    private $_list_server;

    function checkNewServer($node)
    {
        $r = ($this->_list_server != $node->server);
        $this->_list_server = $node->server;
        return $r;
    }

    public function prepareSubscriptions()
    {
        $view = $this->tpl();
        $view->assign('subscriptions', $this->user->subscriptions()
            ->where('node', 'not like', 'urn:xmpp:microblog:0:comments/%')
            ->orderBy('server')->orderBy('node')
            ->get());
        $html = $view->draw('_communitysubscriptions', true);

        return $html;
    }
}
