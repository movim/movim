<?php

use Respect\Validation\Validator;

class CommunityData extends \Movim\Widget\Base
{
    public function load()
    {
        $this->registerEvent('disco_request_handle', 'onDiscoRequest');
    }

    function onDiscoRequest($packet)
    {
        list($server, $node) = $packet->content;

        $this->rpc('MovimTpl.fill', '#community_data', $this->prepareData($server, $node));
    }

    public function prepareData($server, $node)
    {
        $id = new \Modl\InfoDAO;
        $info = $id->get($server, $node);
/*
        if($item && !$item->logo) {
            $item->setPicture();
            $id->set($item);
        }
*/
        $pd = new \Modl\SubscriptionDAO;
        $subscription = $pd->get($server, $node);

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
