<?php

use Respect\Validation\Validator;

class CommunitySubscriptions extends \Movim\Widget\Base
{
    private $_list_server;

    public function load()
    {
    }

    function checkNewServer($node) {
        $r = false;

        if($this->_list_server != $node->server)
            $r = true;

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

    public function display()
    {
    }
}
