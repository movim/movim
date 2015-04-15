<?php

use Moxl\Xec\Action\Pubsub\GetItemsId;
use Moxl\Xec\Action\Pubsub\GetMetadata;
use Moxl\Xec\Action\Pubsub\GetAffiliations;
use Moxl\Xec\Action\Pubsub\GetSubscriptions;
use Moxl\Xec\Action\Pubsub\Subscribe;
use Moxl\Xec\Action\Pubsub\Unsubscribe;

use Moxl\Xec\Action\Pubsub\GetConfig;
use Moxl\Xec\Action\Pubsub\SetConfig;

use Respect\Validation\Validator;

class Group extends WidgetCommon
{
    private $_paging = 15;
    private $_role = null;

    function load()
    {
        $this->registerEvent('pubsub_getitem_handle', 'onItems', 'groups');
        $this->registerEvent('pubsub_getitems_handle', 'onItems', 'groups');
        $this->registerEvent('pubsub_getitemsid_handle', 'onItems', 'groups');

        $this->registerEvent('pubsub_getitems_error', 'onItemsError', 'groups');
        $this->registerEvent('pubsub_subscribe_handle', 'onSubscribed');
        $this->registerEvent('pubsub_unsubscribe_handle', 'onUnsubscribed');
        $this->registerEvent('pubsub_getaffiliations_handle', 'onAffiliations');
        $this->registerEvent('pubsub_getsubscriptions_handle', 'onSubscriptions');

        $this->registerEvent('pubsub_getconfig_handle', 'onConfig');
        $this->registerEvent('pubsub_setconfig_handle', 'onConfigSaved');
        $this->registerEvent('bookmark_set_handle', 'onBookmark');
        $this->addjs('group.js');
    }

    function onItems($packet)
    {
        $arr = $packet->content;
        $this->displayItems($arr['server'], $arr['node']);
        RPC::call('Group.clearLoad');
        RPC::call('MovimTpl.showPanel');
    }

    function onBookmark()
    {
        $this->ajaxClear();

        $g = new Groups;
        $g->ajaxHeader();
        $g->ajaxSubscriptions();
    }

    function onItemsError($packet)
    {
        $arr = $packet->content;
        Notification::append(false, $this->__('group.empty'));
        // Display an error message
        RPC::call('Group.clearLoad');
    }

    function onAffiliations($packet)
    {
        list($affiliations, $server, $node) = array_values($packet->content);

        foreach($affiliations as $r) {
            if($r[0] == $this->user->getLogin())
                $this->_role = (string)$r[1];
        }

        Header::fill($this->prepareHeader($server, $node));

        if(isset($this->_role)
        && ($this->_role == 'owner' || $this->_role == 'publisher')) {
            $view = $this->tpl();
            RPC::call('movim_append', 'group_widget', $view->draw('_group_publish', true));
        }
    }

    function onSubscriptions($packet)
    {
        list($subscriptions, $server, $node) = array_values($packet->content);

        $view = $this->tpl();
        
        $view->assign('subscriptions', $subscriptions);
        $view->assign('server', $server);
        $view->assign('node', $node);

        Dialog::fill($view->draw('_group_subscriptions', true), true);
    }

    function onConfig($packet)
    {
        list($config, $server, $node) = array_values($packet->content);

        $view = $this->tpl();
        
        $xml = new \XMPPtoForm();
        $form = $xml->getHTML($config->x->asXML());

        $view->assign('form', $form);
        $view->assign('server', $server);
        $view->assign('node', $node);
        $view->assign('attributes', $config->attributes());

        Dialog::fill($view->draw('_group_config', true), true);
    }

    function onConfigSaved()
    {
        Notification::append(false, $this->__('group.config_saved'));
    }

    function onSubscribed($packet) 
    {
        $arr = $packet->content;

        // Set the bookmark
        $r = new Rooms;
        $r->setBookmark();

        Notification::append(null, $this->__('group.subscribed'));

        // Set the public list
        /*
        //add the group to the public list (if checked)
        if($this->_data['listgroup'] == true){
            $add = new ListAdd();
            $add->setTo($this->_to)
              ->setNode($this->_node)
              ->setFrom($this->_from)
              ->setData($this->_data)
              ->request();
        }
        
        }*/
    }

    function onUnsubscribed($packet) 
    {
        $arr = $packet->content;

        // Set the bookmark
        $r = new Rooms;
        $r->setBookmark();

        Notification::append(null, $this->__('group.unsubscribed'));
    }

    private function displayItems($server, $node)
    {
        if(!$this->validateServerNode($server, $node)) return;

        $html = $this->prepareGroup($server, $node);
        $header = $this->prepareHeader($server, $node);
        
        Header::fill($header);

        RPC::call('movim_fill', 'group_widget', $html);
    }

    function ajaxGetMetadata($server, $node)
    {
        if(!$this->validateServerNode($server, $node)) return;

        $r = new GetMetadata;
        $r->setTo($server)->setNode($node)
          ->request();
    }

    function ajaxGetConfig($server, $node){
        if(!$this->validateServerNode($server, $node)) return;

        $r = new GetConfig;
        $r->setTo($server)
          ->setNode($node)
          ->request();
    }

    function ajaxSetConfig($data, $server, $node){
        if(!$this->validateServerNode($server, $node)) return;

        $r = new SetConfig;
        $r->setTo($server)
          ->setNode($node)
          ->setData($data)
          ->request();
    }

    function ajaxGetItems($server, $node)
    {
        if(!$this->validateServerNode($server, $node)) return;

        $r = new GetItemsId;
        $r->setTo($server)
          ->setNode($node);
        
        $r->request();

        RPC::call('Group.addLoad');
    }

    function ajaxGetAffiliations($server, $node){
        if(!$this->validateServerNode($server, $node)) return;

        $r = new GetAffiliations;
        $r->setTo($server)->setNode($node)
          ->request();
    }

    function ajaxGetSubscriptions($server, $node)
    {
        if(!$this->validateServerNode($server, $node)) return;

        $r = new GetSubscriptions;
        $r->setTo($server)
          ->setNode($node)
          ->setSync()
          ->request();
    }

    function ajaxAskSubscribe($server, $node)
    {
        if(!$this->validateServerNode($server, $node)) return;

        $view = $this->tpl();

        $view->assign('server', $server);
        $view->assign('node', $node);

        $pd = new \Modl\ItemDAO;
        $item = $pd->getItem($server, $node);

        if(isset($item)) {
            $view->assign('item', $item);
        } else {
            $view->assign('item', null);
        }

        Dialog::fill($view->draw('_group_subscribe', true));
    }

    function ajaxSubscribe($form, $server, $node)
    {
        if(!$this->validateServerNode($server, $node)) return;

        $g = new Subscribe;
        $g->setTo($server)
          ->setNode($node)
          ->setFrom($this->user->getLogin())
          ->setData($form)
          ->request();
    }

    function ajaxAskUnsubscribe($server, $node)
    {
        if(!$this->validateServerNode($server, $node)) return;

        $view = $this->tpl();

        $view->assign('server', $server);
        $view->assign('node', $node);

        $pd = new \Modl\ItemDAO;
        $item = $pd->getItem($server, $node);

        if(isset($item)) {
            $view->assign('item', $item);
        } else {
            $view->assign('item', null);
        }

        Dialog::fill($view->draw('_group_unsubscribe', true));
    }

    function ajaxUnsubscribe($server, $node)
    {
        if(!$this->validateServerNode($server, $node)) return;

        $sd = new \Modl\SubscriptionDAO();

        foreach($sd->get($server, $node) as $s) {
            $g = new Unsubscribe;
            $g->setTo($server)
              ->setNode($node)
              ->setSubid($s->subid)
              ->setFrom($this->user->getLogin())
              ->request();
        }
    }

    function ajaxClear()
    {
        $html = $this->prepareEmpty();
        RPC::call('movim_fill', 'group_widget', $html);
    }

    function prepareEmpty()
    {
        $view = $this->tpl();
        $html = $view->draw('_group_empty', true);

        return $html;
    }

    private function prepareHeader($server, $node)
    {
        $pd = new \Modl\ItemDAO;
        $item = $pd->getItem($server, $node);

        $pd = new \Modl\SubscriptionDAO;
        $subscription = $pd->get($server, $node);

        $view = $this->tpl();

        $view->assign('item', $item);
        $view->assign('subscription', $subscription);
        $view->assign('role', $this->_role);

        return $view->draw('_group_header', true);
    }

    private function prepareGroup($server, $node)
    {
        $pd = new \Modl\PostnDAO();
        $posts = $pd->getNodeUnfiltered($server, $node, 0, $this->_paging);

        $view = $this->tpl();
        $view->assign('posts', $posts);
        $html = $view->draw('_group_posts', true);

        return $html;
    }

    private function validateServerNode($server, $node)
    {
        $validate_server = Validator::string()->noWhitespace()->length(6, 40);
        $validate_node = Validator::string()->length(3, 100);

        if(!$validate_server->validate($server)
        || !$validate_node->validate($node)
        ) return false;
        else return true;
    }

    function display()
    {
    }
}
