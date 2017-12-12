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
        //$this->registerEvent('pubsub_getitemsid_handle', 'onItemsId');
        $this->registerEvent('pubsub_getitems_handle', 'onItemsId');
        $this->registerEvent('pubsub_getitems_error', 'onItemsError');
        $this->registerEvent('pubsub_getitemsid_error', 'onItemsError');

        $this->addjs('communityposts.js');
    }

    /*function onItems($packet)
    {
        list($origin, $node) = array_values($packet->content);
        $this->displayItems($origin, $node);
    }*/

    function onItemsId($packet)
    {
        list($origin, $node, $ids, $first, $last, $count, $paginated)
            = array_values($packet->content);

        $this->displayItems($origin, $node, $ids, $first, $last, $count, $paginated);
    }

    function onItemsError($packet)
    {
        list($origin, $node) = array_values($packet->content);

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

    private function displayItems(
        $origin,
        $node,
        $ids = false,
        $first = false,
        $last = false,
        $count = false,
        $paginated = false)
    {
        if(!$this->validateServerNode($origin, $node)) return;

        $html = $this->prepareCommunity($origin, $node, 0, $ids, $first, $last, $count);

        $slugify = new Slugify;
        $this->rpc(
            ($paginated) ? 'MovimTpl.append' : 'MovimTpl.fill',
            '#communityposts.'.$slugify->slugify('c'.$origin.'_'.$node), $html);
        $this->rpc('MovimUtils.enhanceArticlesContent');
    }

    function ajaxGetContact($jid)
    {
        $c = new Contact;
        $c->ajaxGetDrawer($jid);
    }

    function ajaxGetItems($origin, $node, $before = 'empty')
    {
        if(!$this->validateServerNode($origin, $node)) return;

        // https://github.com/maranda/metronome/issues/236
        /*if($node == 'urn:xmpp:microblog:0') {
            $r = new GetItems;
        } else {*/
            $r = new GetItems;
        //}

        if(!isset($before)) $before = 'empty';

        $r->setTo($origin)
          ->setNode($node)
          ->setPaging($this->_paging)
          ->setBefore($before)
          ->request();
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
        $view->assign('servers', $id->getCommunitiesServers());
        $html = $view->draw('_communityposts_empty', true);

        return $html;
    }

    public function preparePost($p)
    {
        $pw = new \Post;
        return $pw->preparePost($p, true, false, true);
    }

    private function prepareCommunity(
        $origin,
        $node,
        $page = 0,
        $ids = false,
        $first = false,
        $last = false,
        $count = false)
    {
        $pd = new \Modl\PostnDAO;

        /*if($public) {
            $posts = $pd->getPublic($origin, $node, $page*$this->_paging, $this->_paging);
        } else*/
        if($ids == false) {
            return $this->prepareEmpty();
        } else {
            foreach($ids as $key => $id) {
                if(empty($id)) {
                    unset($ids[$key]);
                }
            }

            $posts = $pd->getIds($origin, $node, $ids);
        }

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

        if(is_array($posts)) {
            foreach($posts as $key => $post) {
                $posts[$post->nodeid] = $post;
                unset($posts[$key]);
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

        $view->assign('first', $first);
        $view->assign('last', $last);
        $view->assign('count', $count);

        $html = $view->draw('_communityposts', true);

        return $html;
    }

    private function validateServerNode($origin, $node)
    {
        $validate_server = Validator::stringType()->noWhitespace()->length(6, 40);
        $validate_node = Validator::stringType()->length(3, 100);

        return ($validate_server->validate($origin)
             && $validate_node->validate($node));
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
        $this->view->assign('class', $slugify->slugify('c'.$this->get('s').'_'.$node));
    }
}

