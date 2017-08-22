<?php

use Respect\Validation\Validator;

class CommunityData extends \Movim\Widget\Base
{
    public function load()
    {
        $this->registerEvent('disco_request_handle', 'onDiscoRequest', 'community');
    }

    function onDiscoRequest($packet)
    {
        list($origin, $node) = $packet->content;

        if((substr($node, 0, 30) != 'urn:xmpp:microblog:0:comments/')) {
            $this->rpc('MovimTpl.fill', '#community_data', $this->prepareData($origin, $node));
        }
    }

    public function prepareData($origin, $node)
    {
        $id = new \Modl\InfoDAO;
        $info = $id->get($origin, $node);
        /*
        if($item && !$item->logo) {
            $item->setPicture();
            $id->set($item);
        }
        */
        $pd = new \Modl\SubscriptionDAO;
        $subscription = $pd->get($origin, $node);

        $view = $this->tpl();
        $view->assign('info', $info);
        $view->assign('subscription', $subscription);

        return $view->draw('_communitydata', true);
    }

    public function display()
    {
        $this->view->assign('server', $this->get('s'));
        $this->view->assign('node', $this->get('n'));
    }
}
