<?php

use Moxl\Xec\Action\Pubsub\GetItemsId;

use Moxl\Xec\Action\Pubsub\Delete;

use Respect\Validation\Validator;
use Cocur\Slugify\Slugify;

include_once WIDGETS_PATH.'Post/Post.php';

class CommunityPosts extends \Movim\Widget\Base
{
    private $_paging = 8;

    function load()
    {
        $this->registerEvent('pubsub_getitem_handle', 'onItems');
        $this->registerEvent('pubsub_getitems_handle', 'onItems');
        $this->registerEvent('pubsub_getitemsid_handle', 'onItems');
        $this->registerEvent('pubsub_getitems_error', 'onItemsError');
        $this->registerEvent('pubsub_getitemsid_error', 'onItemsError');

        $this->addjs('communityposts.js');
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
            } else {
                $id = new \Modl\ItemDAO;
                $id->deleteItem($server, $node);
                $this->ajaxClear();
            }
        }
    }

    private function displayItems($server, $node)
    {
        if(!$this->validateServerNode($server, $node)) return;

        $html = $this->prepareCommunity($server, $node);
        $slugify = new Slugify;

        RPC::call('MovimTpl.fill', '#communityposts.'.$slugify->slugify($server.'_'.$node), $html);
        RPC::call('MovimUtils.enableVideos');
    }

    function ajaxGetContact($jid)
    {
        $c = new Contact;
        $c->ajaxGetDrawer($jid);
    }

    function ajaxGetItems($server, $node)
    {
        if(!$this->validateServerNode($server, $node)) return;
        $slugify = new Slugify;

        $r = new GetItemsId;
        $r->setTo($server)
          ->setNode($node);

        $r->request();
    }

    function ajaxGetHistory($server, $node, $page)
    {
        $html = $this->prepareCommunity($server, $node, $page);
        RPC::call('MovimTpl.append', '#communityposts', $html);
        RPC::call('MovimUtils.enableVideos');
    }

    function ajaxClear()
    {
        $html = $this->prepareEmpty();
        RPC::call('MovimTpl.fill', '#communityposts', $html);
    }

    function prepareEmpty()
    {
        $id = new \Modl\ItemDAO();

        $view = $this->tpl();
        $view->assign('servers', $id->getGroupServers());
        $html = $view->draw('_communityposts_empty', true);

        return $html;
    }

    public function preparePost($p) {
        $pw = new \Post;
        return $pw->preparePost($p, true, true, true);
    }

    private function prepareCommunity($server, $node, $page = 0)
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

        $html = $view->draw('_communityposts', true);

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
        $pd = new \Modl\PostnDAO;
        return $pd->getComments($post);
    }

    function display()
    {
        $slugify = new Slugify;

        $this->view->assign('class', $slugify->slugify($this->get('s').'_'.$this->get('n')));
        $this->view->assign('html', $this->prepareCommunity($this->get('s'), $this->get('n')));
    }
}

