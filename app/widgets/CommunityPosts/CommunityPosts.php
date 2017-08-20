<?php

use Moxl\Xec\Action\Pubsub\GetItemsId;
use Moxl\Xec\Action\Pubsub\GetItems;
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
        //$this->registerEvent('pubsub_getitems_handle', 'onItems');
        $this->registerEvent('pubsub_getitems_error', 'onItemsError');
        $this->registerEvent('pubsub_getitemsid_error', 'onItemsError');

        $this->addjs('communityposts.js');
    }

    function onItem($packet)
    {
        list($origin, $node, $id) = array_values($packet->content);

        $pd = new \Modl\PostnDAO;
        $p = $pd->get($origin, $node, $id);

        if($p && $p->isComment()) $p = $p->getParent();

        if($p) {
            $this->rpc('MovimTpl.fill', '#'.cleanupId($p->nodeid), $this->preparePost($p));
        }
    }

    /*function onItems($packet)
    {
        list($origin, $node) = array_values($packet->content);
        $this->displayItems($origin, $node);
    }*/

    function onItemsId($packet)
    {
        list($origin, $node, $ids) = array_values($packet->content);

        $ids = array_slice($ids, 0, $this->_paging);
        $this->displayItems($origin, $node, $ids);
    }

    function onItemsError($packet)
    {
        list($origin, $node) = array_values($packet->content);
        Notification::append(false, $this->__('group.empty'));

        if($node != 'urn:xmpp:microblog:0') {
            $sd = new \Modl\SubscriptionDAO;

            if($sd->get($origin, $node)) {
                $this->rpc('CommunityAffiliations_ajaxDelete', $origin, $node, true);
                $this->rpc('CommunityAffiliations_ajaxGetAffiliations', $origin, $node);
            } else {
                $id = new \Modl\InfoDAO;
                $id->delete($origin, $node);
                $this->ajaxClear();
            }
        } else {
            $this->displayItems($origin, $node, false, true);
        }
    }

    private function displayItems($origin, $node, $ids = false, $public = false)
    {
        if(!$this->validateServerNode($origin, $node)) return;

        $html = $this->prepareCommunity($origin, $node, 0, $ids, $public);

        $slugify = new Slugify;
        $this->rpc('MovimTpl.fill', '#communityposts.'.$slugify->slugify($origin.'_'.$node), $html);
        $this->rpc('MovimUtils.enhanceArticlesContent');
    }

    function ajaxGetContact($jid)
    {
        $c = new Contact;
        $c->ajaxGetDrawer($jid);
    }

    function ajaxGetItems($origin, $node)
    {
        if(!$this->validateServerNode($origin, $node)) return;

        // https://github.com/maranda/metronome/issues/236
        /*if($node == 'urn:xmpp:microblog:0') {
            $r = new GetItems;
        } else {*/
            $r = new GetItemsId;
        //}

        $r->setTo($origin)
          ->setNode($node);

        $r->request();
    }

    function ajaxGetHistory($origin, $node, $page)
    {
        $html = $this->prepareCommunity($origin, $node, $page);
        $this->rpc('MovimTpl.append', '#communityposts', $html);
        $this->rpc('MovimUtils.enhanceArticlesContent');
    }

    function ajaxClear()
    {
        $html = $this->prepareEmpty();
        $this->rpc('MovimTpl.fill', '#communityposts', $html);
    }

    function prepareEmpty()
    {
        $id = new \Modl\InfoDAO;

        $view = $this->tpl();
        $view->assign('servers', $id->getGroupServers());
        $html = $view->draw('_communityposts_empty', true);

        return $html;
    }

    public function preparePost($p)
    {
        $pw = new \Post;
        return $pw->preparePost($p, true, false, true);
    }

    private function prepareCommunity($origin, $node, $page = 0, $ids = false, $public = false)
    {
        $pd = new \Modl\PostnDAO;

        /*if($ids == false) {*/
        if($public) {
            $posts = $pd->getPublic($origin, $node, $page*$this->_paging, $this->_paging);
        } else {
            $posts = $pd->getNodeUnfiltered($origin, $node, $page*$this->_paging, $this->_paging);
        }
        /*
        } else {
            $posts = $pd->getIds($origin, $node, $ids);
        }*/

        $id = new \Modl\InfoDAO;
        $info = $id->get($origin, $node);

        $pd = new \Modl\SubscriptionDAO;
        $subscription = $pd->get($origin, $node);

        $nsfwMessage = false;

        if($this->user->getConfig('nsfw') == false
        && is_array($posts)) {
            foreach($posts as $key => $post) {
                if($post->nsfw) {
                    unset($posts[$key]);
                    $nsfwMessage = true;
                }
            }
        }

        foreach($ids as $key => $id) {
            if(empty($id)) {
                unset($ids[$key]);
            }
        }

        $view = $this->tpl();
        $view->assign('server', $origin);
        $view->assign('node', $node);
        $view->assign('page', $page);
        $view->assign('ids', $ids);
        $view->assign('posts', $posts);
        $view->assign('info', $info);
        $view->assign('subscription', $subscription);
        $view->assign('paging', $this->_paging);
        $view->assign('nsfwMessage', $nsfwMessage);

        $html = $view->draw('_communityposts', true);

        return $html;
    }

    private function validateServerNode($origin, $node)
    {
        $validate_server = Validator::stringType()->noWhitespace()->length(6, 40);
        $validate_node = Validator::stringType()->length(3, 100);

        if(!$validate_server->validate($origin)
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

        $node = $this->get('n') != null ? $this->get('n') : 'urn:xmpp:microblog:0';
        $this->view->assign('class', $slugify->slugify($this->get('s').'_'.$node));
    }
}

