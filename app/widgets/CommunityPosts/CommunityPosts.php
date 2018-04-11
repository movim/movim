<?php

use Moxl\Xec\Action\Pubsub\GetItemsId;
use Moxl\Xec\Action\Pubsub\GetItems;
use Moxl\Xec\Action\Pubsub\Delete;

use Respect\Validation\Validator;
use Cocur\Slugify\Slugify;
use App\User;

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

    function onItemsId($packet)
    {
        list($origin, $node, $ids, $first, $last, $count, $paginated)
            = array_values($packet->content);

        $this->displayItems($origin, $node, $ids, $first, $last, $count, $paginated);
    }

    function onItemsError($packet)
    {
        list($origin, $node) = array_values($packet->content);

        if ($node != 'urn:xmpp:microblog:0') {
            if ($this->user->subscriptions()
                           ->where('server', $origin)
                           ->where('node', $node)
                           ->first())
            {
                $this->rpc('CommunityAffiliations_ajaxDelete', $origin, $node, true);
                $this->rpc('CommunityAffiliations_ajaxGetAffiliations', $origin, $node);
            } else {
                \App\Info::where('server', $origin)->where('node', $node)->delete();
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
        if (!$this->validateServerNode($origin, $node)) return;

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
        if (!$this->validateServerNode($origin, $node)) return;

        // https://github.com/maranda/metronome/issues/236
        /*if ($node == 'urn:xmpp:microblog:0') {
            $r = new GetItems;
        } else {*/
            $r = new GetItems;
        //}

        if (!isset($before)) $before = 'empty';

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
        $view = $this->tpl();
        return $view->draw('_communityposts_empty', true);
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
        $ids = is_array($ids) ? $ids : [];
        foreach($ids as $key => $id) {
            if (empty($id)) {
                unset($ids[$key]);
            }
        }

        $posts = \App\Post::where('server', $origin)->where('node', $node)
                          ->whereIn('nodeid', $ids)->get();

        $nsfwMessage = false;

        if (User::me()->nsfw == false) {
            foreach ($posts as $key => $post) {
                if ($post->nsfw) {
                    $posts->forget($key);
                    $nsfwMessage = true;
                }
            }
        }

        $postsWithKeys = [];

        foreach ($posts as $key => $post) {
            $postsWithKeys[$post->nodeid] = $post;
        }

        $view = $this->tpl();

        if ($nsfwMessage) {
            $this->rpc('MovimTpl.remove', '#nsfwmessage');
            $this->rpc(
                'MovimTpl.prepend',
                '#communityposts',
                $view->draw('_communityposts_nsfw', true)
            );
        }

        $view->assign('server', $origin);
        $view->assign('node', $node);
        $view->assign('page', $page);
        $view->assign('ids', $ids);
        $view->assign('posts', $postsWithKeys);
        $view->assign('info', \App\Info::where('server', $origin)
                                       ->where('node', $node)
                                       ->first());
        $view->assign('subscription', $this->user->subscriptions()
                                           ->where('server', $origin)
                                           ->where('node', $node)
                                           ->first());
        $view->assign('paging', $this->_paging);

        $view->assign('publicposts', ($ids == false)
            ? \App\Post::where('server', $origin)
                       ->where('node', $node)
                       ->where(function ($query) {
                            $query->where('nsfw', $this->user->nsfw)
                                  ->orWhere('nsfw', false);
                       })
                       ->orderBy('published', 'desc')
                       ->skip($page * $this->_paging)
                       ->take($this->_paging)
                       ->get()
            : false);

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

    function display()
    {
        $slugify = new Slugify;

        $node = $this->get('n') != null ? $this->get('n') : 'urn:xmpp:microblog:0';
        $this->view->assign('class', $slugify->slugify('c'.$this->get('s').'_'.$node));
    }
}

