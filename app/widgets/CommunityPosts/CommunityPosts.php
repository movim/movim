<?php

use Moxl\Xec\Action\Pubsub\GetItemsId;
use Moxl\Xec\Action\Pubsub\Delete;

use Respect\Validation\Validator;
use Cocur\Slugify\Slugify;

include_once WIDGETS_PATH.'Post/Post.php';

class CommunityPosts extends \Movim\Widget\Base
{
    private $_paging = 10;

    function load()
    {
        $this->registerEvent('pubsub_getitem_handle', 'onItem');
        $this->registerEvent('pubsub_getitemsid_handle', 'onItemsId');
        $this->registerEvent('pubsub_getitems_error', 'onItemsError');
        $this->registerEvent('pubsub_getitemsid_error', 'onItemsError');

        $this->addjs('communityposts.js');
    }

    function onItem($packet)
    {
        list($server, $node, $id) = array_values($packet->content);
        /*
        RPC::call('MovimTpl.fill', '#'.cleanupId($id), $this->preparePost($server, $node, $id));
        RPC::call('MovimUtils.enableVideos');*/
        $this->displayItems($server, $node);
    }

    function onItemsId($packet)
    {
        list($server, $node, $ids) = array_values($packet->content);

        $ids = array_slice($ids, 0, $this->_paging);

        $this->displayItems($server, $node, $ids);
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

    private function displayItems($server, $node, $ids = false)
    {
        if(!$this->validateServerNode($server, $node)) return;

        $html = $this->prepareCommunity($server, $node, 0, $ids);

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
        /*$pd = new \Modl\PostnDAO;
        $p = $pd->get($server, $node, $id);*/

        $pw = new \Post;
        return $pw->preparePost($p, true, true, true);
    }

    private function prepareCommunity($server, $node, $page = 0, $ids)
    {
        $pd = new \Modl\PostnDAO;

        /*if($ids == false) {*/
            $posts = $pd->getNodeUnfiltered($server, $node, $page*$this->_paging, $this->_paging);
        /*
        } else {
            $posts = $pd->getIds($server, $node, $ids);
        }*/

        $id = new \Modl\ItemDAO;
        $item = $id->getItem($server, $node);

        $pd = new \Modl\SubscriptionDAO;
        $subscription = $pd->get($server, $node);

        $view = $this->tpl();
        $view->assign('server', $server);
        $view->assign('node', $node);
        $view->assign('page', $page);
        //$view->assign('ids', $ids);
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
    }
}

