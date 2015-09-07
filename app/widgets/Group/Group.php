<?php

use Moxl\Xec\Action\Pubsub\GetItemsId;
use Moxl\Xec\Action\Pubsub\GetMetadata;
use Moxl\Xec\Action\Pubsub\GetAffiliations;
use Moxl\Xec\Action\Pubsub\GetSubscriptions;
use Moxl\Xec\Action\Pubsub\Subscribe;
use Moxl\Xec\Action\Pubsub\Unsubscribe;

use Moxl\Xec\Action\Pubsub\GetConfig;
use Moxl\Xec\Action\Pubsub\SetConfig;

use Moxl\Xec\Action\Pubsub\Delete;

use Respect\Validation\Validator;

class Group extends WidgetBase
{
    private $_paging = 15;
    private $_role = null;

    function load()
    {
        $this->registerEvent('pubsub_getitem_handle', 'onItems', 'groups');
        $this->registerEvent('pubsub_getitems_handle', 'onItems', 'groups');
        $this->registerEvent('pubsub_getitemsid_handle', 'onItems', 'groups');
        $this->registerEvent('pubsub_getitems_error', 'onItemsError', 'groups');
        $this->registerEvent('pubsub_getitemsid_error', 'onItemsError', 'groups');

        $this->registerEvent('pubsub_subscribe_handle', 'onSubscribed');
        $this->registerEvent('pubsub_unsubscribe_handle', 'onUnsubscribed');
        $this->registerEvent('pubsub_getaffiliations_handle', 'onAffiliations');
        $this->registerEvent('pubsub_getsubscriptions_handle', 'onSubscriptions');

        $this->registerEvent('pubsub_delete_handle', 'onDelete');

        $this->registerEvent('post_ticker', 'onTicker');
        $this->registerEvent('pubsub_getitem_ticker', 'onTicker');

        $this->registerEvent('pubsub_getconfig_handle', 'onConfig');
        $this->registerEvent('pubsub_setconfig_handle', 'onConfigSaved');
        $this->registerEvent('bookmark_set_handle', 'onBookmark');
        $this->addjs('group.js');
    }

    function onItems($packet)
    {
        list($server, $node) = array_values($packet->content);

        $this->displayItems($server, $node);

        RPC::call('Group.clearLoad');
        RPC::call('MovimTpl.showPanel');
    }

    function onDelete($packet)
    {
        $this->ajaxClear();
    }

    function onTicker($packet)
    {
        list($server, $node, $ticker) = array_values($packet->content);

        $view = $this->tpl();
        $view->assign('server', $server);
        $view->assign('node', $node);
        $view->assign('ticker', $ticker);

        $html = $view->draw('_group_ticker', true);

        RPC::call('MovimTpl.fill', '#group_widget.'.stringToUri($server.'_'.$node), $html);
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
        list($server, $node) = array_values($packet->content);
        Notification::append(false, $this->__('group.empty'));

        $this->ajaxDelete($server, $node, true);
        $this->ajaxGetAffiliations($server, $node);
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

        //if(isset($this->_role)
        //&& ($this->_role == 'owner' || $this->_role == 'publisher')) {
        //    $view = $this->tpl();
        //    $view->assign('server', $server);
        //    $view->assign('node', $node);
        //    RPC::call('movim_append', 'group_widget', $view->draw('_group_publish', true));
        //}
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

        $view = $this->tpl();
        $view->assign('server', $server);
        $view->assign('node', $node);
        $html .= $view->draw('_group_publish', true);

        RPC::call('MovimTpl.fill', '#group_widget.'.stringToUri($server.'_'.$node), $html);
        RPC::call('Group.enableVideos');
    }


    function ajaxDelete($server, $node, $clean = false)
    {
        if(!$this->validateServerNode($server, $node)) return;

        $view = $this->tpl();
        $view->assign('server', $server);
        $view->assign('node', $node);
        $view->assign('clean', $clean);

        Dialog::fill($view->draw('_group_delete', true));
    }

    function ajaxDeleteConfirm($server, $node)
    {
        if(!$this->validateServerNode($server, $node)) return;

        $d = new Delete;
        $d->setTo($server)->setNode($node)
          ->request();
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

        RPC::call('Group.addLoad', stringToUri($server.'_'.$node));

        $r = new GetItemsId;
        $r->setTo($server)
          ->setNode($node);

        $r->request();
    }

    function ajaxGetHistory($server, $node, $page)
    {
        $html = $this->prepareGroup($server, $node, $page);
        RPC::call('movim_append', 'group_widget', $html);
        RPC::call('Group.enableVideos');
    }

    function ajaxGetAffiliations($server, $node){
        if(!$this->validateServerNode($server, $node)) return;

        $r = new GetAffiliations;
        $r->setTo($server)->setNode($node)
          ->request();
    }

    function ajaxGetSubscriptions($server, $node, $notify = true)
    {
        if(!$this->validateServerNode($server, $node)) return;

        $r = new GetSubscriptions;
        $r->setTo($server)
          ->setNode($node)
          ->setNotify($notify)
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
        $id = new \modl\ItemDAO();

        $view = $this->tpl();
        $view->assign('servers', $id->getGroupServers());
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

    private function prepareGroup($server, $node, $page = 0)
    {
        $pd = new \Modl\PostnDAO();
        $posts = $pd->getNodeUnfiltered($server, $node, $page*$this->_paging, $this->_paging);

        $view = $this->tpl();
        $view->assign('server', $server);
        $view->assign('node', $node);
        $view->assign('page', $page);
        $view->assign('posts', $posts);
        $view->assign('paging', $this->_paging);
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

    function getComments($post)
    {
        $pd = new \Modl\PostnDAO();
        return $pd->getComments($post);
    }

    function display()
    {
        $this->view->assign('server', false);
        $this->view->assign('node', false);
        if($this->validateServerNode($this->get('s'), $this->get('n'))) {
            $this->view->assign('server', $this->get('s'));
            $this->view->assign('node', $this->get('n'));
        }
    }
}
