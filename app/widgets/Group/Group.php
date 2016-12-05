<?php

use Moxl\Xec\Action\Pubsub\GetItemsId;
use Moxl\Xec\Action\Pubsub\GetAffiliations;
use Moxl\Xec\Action\Pubsub\GetSubscriptions;

use Moxl\Xec\Action\Pubsub\GetConfig;
use Moxl\Xec\Action\Pubsub\SetConfig;

use Moxl\Xec\Action\Pubsub\Delete;

use Respect\Validation\Validator;
use Cocur\Slugify\Slugify;

include_once WIDGETS_PATH.'Post/Post.php';

class Group extends \Movim\Widget\Base
{
    private $_paging = 8;
    private $_role = null;

    function load()
    {
        $this->registerEvent('pubsub_getitem_handle', 'onItems');
        $this->registerEvent('pubsub_getitems_handle', 'onItems');
        $this->registerEvent('pubsub_getitemsid_handle', 'onItems');
        $this->registerEvent('pubsub_getitems_error', 'onItemsError');
        $this->registerEvent('pubsub_getitemsid_error', 'onItemsError');
        $this->registerEvent('pubsub_getsubscriptions_handle', 'onSubscriptions');

        $this->registerEvent('pubsub_getconfig_handle', 'onConfig');
        $this->registerEvent('pubsub_setconfig_handle', 'onConfigSaved');

        $this->addjs('group.js');
    }

    function onItems($packet)
    {
        list($server, $node) = array_values($packet->content);
        $this->displayItems($server, $node);
    }

    function onItemsError($packet)
    {
        list($server, $node) = array_values($packet->content);
        Notification::append(false, $this->__('group.empty'));

        if($node != 'urn:xmpp:microblog:0') {
            $sd = new \Modl\SubscriptionDAO;

            if($sd->get($server, $node)) {
                $this->ajaxDelete($server, $node, true);
                $this->ajaxGetAffiliations($server, $node);
                // Display an error message
                RPC::call('Group.clearLoad');
            } else {
                $id = new \Modl\ItemDAO;
                $id->deleteItem($server, $node);
                $this->ajaxClear();
            }
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

    private function displayItems($server, $node)
    {
        if(!$this->validateServerNode($server, $node)) return;

        $html = $this->prepareGroup($server, $node);
        $slugify = new Slugify();

        RPC::call('MovimTpl.fill', '#group_widget.'.$slugify->slugify($server.'_'.$node), $html);
    }

    function ajaxGetContact($jid)
    {
        $c = new Contact;
        $c->ajaxGetDrawer($jid);
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
        $slugify = new Slugify();

        //RPC::call('Group.addLoad', $slugify->slugify($server.'_'.$node));
        //RPC::call('MovimUtils.pushState', $this->route('group', [$server, $node]));
        //RPC::call('MovimTpl.fill', '#group_widget.'.$slugify->slugify($server.'_'.$node), '');

        $r = new GetItemsId;
        $r->setTo($server)
          ->setNode($node);

        $r->request();
    }

    function ajaxGetHistory($server, $node, $page)
    {
        $html = $this->prepareGroup($server, $node, $page);
        RPC::call('MovimTpl.append', '#group_widget', $html);
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

    function ajaxClear()
    {
        $html = $this->prepareEmpty();
        RPC::call('MovimTpl.fill', '#group_widget header', '');
        RPC::call('MovimTpl.fill', '#group_widget', $html);
    }

    function prepareEmpty()
    {
        $id = new \modl\ItemDAO();

        $view = $this->tpl();
        $view->assign('servers', $id->getGroupServers());
        $html = $view->draw('_group_empty', true);

        return $html;
    }

    public function preparePost($p) {
        $pw = new \Post;
        return $pw->preparePost($p, true, true, true);
    }

    private function prepareGroup($server, $node, $page = 0)
    {
        $pd = new \Modl\PostnDAO;
        $posts = $pd->getNodeUnfiltered($server, $node, $page*$this->_paging, $this->_paging);

        $id = new \Modl\ItemDAO;
        $item = $id->getItem($server, $node);

        $pd = new \Modl\SubscriptionDAO;
        $subscription = $pd->get($server, $node);

        $view = $this->tpl();
        $view->assign('server', $server);
        $view->assign('node', $node);
        $view->assign('page', $page);
        $view->assign('posts', $posts);
        $view->assign('item', $item);
        $view->assign('subscription', $subscription);
        $view->assign('paging', $this->_paging);

        $html = $view->draw('_group_posts', true);

        return $html;
    }

    private function validateServerNode($server, $node)
    {
        $validate_server = Validator::stringType()->noWhitespace()->length(6, 40);
        $validate_node = Validator::stringType()->length(3, 100);

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
        $slugify = new Slugify();
        /*$this->view->assign('server', false);
        $this->view->assign('node', false);
        if($this->validateServerNode($this->get('s'), $this->get('n'))) {
            $this->view->assign('server', $this->get('s'));
            $this->view->assign('node', $this->get('n'));
        }*/
        $this->view->assign('class', $slugify->slugify($this->get('s').'_'.$this->get('n')));
        $this->view->assign('html', $this->prepareGroup($this->get('s'), $this->get('n')));
    }
}
