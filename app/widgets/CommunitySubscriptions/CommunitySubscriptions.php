<?php

use Respect\Validation\Validator;

class CommunitySubscriptions extends \Movim\Widget\Base
{
    private $_list_server;

    public function load()
    {
    }

    function checkNewServer($node)
    {
        $r = ($this->_list_server != $node->server);
        $this->_list_server = $node->server;
        return $r;
    }

    public function prepareSubscriptions()
    {
        $sd = new \Modl\SubscriptionDAO;

        $view = $this->tpl();
        $view->assign('subscriptions', $sd->getSubscribed());
        $html = $view->draw('_communitysubscriptions', true);

        return $html;
    }
}
