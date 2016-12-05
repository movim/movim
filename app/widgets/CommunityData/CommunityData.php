<?php

use Respect\Validation\Validator;

class CommunityData extends \Movim\Widget\Base
{
    public function load()
    {
        $this->registerEvent('pubsub_getmetadata_handle', 'onMetadata');
    }

    function onMetadata($packet)
    {
        list($server, $node) = $packet->content;

        $this->rpc('MovimTpl.fill', '#community_data', $this->prepareData($server, $node));
    }

    public function prepareData($server, $node)
    {
        $id = new \Modl\ItemDAO;
        $item = $id->getItem($server, $node);

        if($item && !$item->logo) {
            $item->setPicture();
            $id->set($item);
        }

        $pd = new \Modl\SubscriptionDAO;
        $subscription = $pd->get($server, $node);

        $view = $this->tpl();
        $view->assign('item', $item);
        $view->assign('subscription', $subscription);

        return $view->draw('_communitydata', true);
    }

    public function display()
    {
        $this->view->assign('server', $this->get('s'));
        $this->view->assign('node', $this->get('n'));
    }
}
