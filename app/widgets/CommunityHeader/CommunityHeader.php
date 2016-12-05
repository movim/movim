<?php

use Respect\Validation\Validator;

class CommunityHeader extends \Movim\Widget\Base
{
    public function load()
    {
        $this->registerEvent('pubsub_getmetadata_handle', 'onMetadata');
    }

    function onMetadata($packet)
    {
        list($server, $node) = $packet->content;

        RPC::call('MovimTpl.fill', '#community_header', $this->prepareHeader($server, $node));
    }

    public function prepareHeader($server, $node)
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
        $view->assign('node', $node);
        $view->assign('server', $server);

        return $view->draw('_communityheader', true);
    }

    public function display()
    {
        $this->view->assign('server', $this->get('s'));
        $this->view->assign('node', $this->get('n'));
    }
}
