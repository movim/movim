<?php

namespace App\Widgets\CommunityPosts;

use App\Post as AppPost;
use App\Widgets\ContactActions\ContactActions;
use App\Widgets\Post\Post;
use Movim\Widget\Base;
use Moxl\Xec\Action\Pubsub\GetItems;
use Moxl\Xec\Payload\Packet;

class CommunityPosts extends Base
{
    private $_paging = 12;
    private $_beforeAfter = 'b=';

    public function load()
    {
        $this->registerEvent('pubsub_getitems_handle', 'onItemsId');
        $this->registerEvent('pubsub_getitems_error', 'onItemsError');
        $this->registerEvent('pubsub_getitemsid_error', 'onItemsError');
        $this->registerEvent('pubsub_setconfig_handle', 'onConfigSaved', 'community');
        $this->registerEvent('pubsub_getitems_errorpresencesubscriptionrequired', 'tonItemsErrorPresenceSubscriptionRequired');
        $this->registerEvent('post_resolved', 'tonPostResolved');

        $this->addjs('communityposts.js');
    }

    public function onItemsId(Packet $packet)
    {
        list($origin, $node, $ids, $first, $last, $count, $paginated, $before, $after, $query)
            = array_values($packet->content);

        $this->displayItems($origin, $node, $ids, $first, $last, $count, $paginated, $before, $after, $query);
    }

    public function tonPostResolved(Packet $packet)
    {
        $post = AppPost::find($packet->content);

        $info = \App\Info::where('server', $post->server)
            ->where('node', $post->node)
            ->first();

        $this->rpc(
            'MovimTpl.replace',
            '#' . cleanupId($post->nodeid),
            $info->isGallery()
                ? $this->prepareTicket($post)
                : $this->preparePost($post)
        );
    }

    public function onConfigSaved(Packet $packet)
    {
        $this->rpc('CommunityPosts.getItems');
    }

    public function tonItemsErrorPresenceSubscriptionRequired(Packet $packet)
    {
        list($origin, $node) = array_values($packet->content);

        $view = $this->tpl();

        $this->rpc(
            'MovimTpl.fill',
            '#communityposts.' . slugify('c' . $origin . '_' . $node),
            $view->draw('_communityposts_presencerequired')
        );
    }

    public function onItemsError(Packet $packet)
    {
        list($origin, $node) = array_values($packet->content);

        if ($node != AppPost::MICROBLOG_NODE) {
            if ($this->me->subscriptions()
                ->where('server', $origin)
                ->where('node', $node)
                ->first()
            ) {
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
        $paginated = false,
        $before = null,
        $after = null,
        $query = null
    ) {
        if (!validateServerNode($origin, $node)) {
            return;
        }

        $html = $this->prepareCommunity($origin, $node, 0, $ids, $first, $last, $count, $before, $after, $query);

        $this->rpc(
            'MovimTpl.fill',
            '#communityposts.' . slugify('c' . $origin . '_' . $node),
            $html
        );
        $this->rpc('MovimUtils.enhanceArticlesContent');
    }

    public function ajaxGetContact($jid)
    {
        $c = new ContactActions;
        $c->ajaxGetDrawer($jid);
    }

    public function ajaxGetItems($origin, $node, $before = 'empty', $query = null)
    {
        if (!validateServerNode($origin, $node)) {
            return;
        }

        $r = new GetItems;
        $r->setTo($origin)
            ->setNode($node)
            ->setPaging($this->_paging);

        if ($before !== null) {
            $r = (strpos($before, $this->_beforeAfter) === 0)
                ? $r->setAfter(substr($before, strlen($this->_beforeAfter)))
                : $r->setBefore($before);
        }

        if ($query) {
            $r->setQuery($query);
        }

        $r->request();
    }

    public function ajaxClear()
    {
        $html = $this->prepareEmpty();
        $this->rpc('MovimTpl.fill', '#communityposts', $html);
    }

    public function prepareEmpty($origin = '')
    {
        $view = $this->tpl();
        $view->assign('me', $origin == $this->me->id);
        return $view->draw('_communityposts_empty');
    }

    public function preparePost($p)
    {
        return (new Post)->preparePost($p, false, true);
    }

    public function prepareTicket($p)
    {
        return (new Post)->prepareTicket($p);
    }

    private function prepareCommunity(
        $origin,
        $node,
        $page = 0,
        $ids = false,
        $first = false,
        $last = false,
        $count = false,
        $before = null,
        $after = null,
        $query = null
    ) {
        $ids = is_array($ids) ? $ids : [];
        foreach ($ids as $key => $id) {
            if (empty($id)) {
                unset($ids[$key]);
            }
        }

        if (empty($ids)) {
            return $this->prepareEmpty($origin);
        }

        $posts = \App\Post::where('server', $origin)->where('node', $node)
            ->whereIn('nodeid', $ids)->get();
        $postsWithKeys = [];

        if ($posts->isNotEmpty()) {
            $posts = resolveInfos($posts);

            foreach ($posts as $key => $post) {
                $postsWithKeys[$post->nodeid] = $post;
            }
        }

        $info = \App\Info::where('server', $origin)
            ->where('node', $node)
            ->first();

        $view = $this->tpl();

        $view->assign('server', $origin);
        $view->assign('node', $node);
        $view->assign('page', $page);
        $view->assign('ids', $ids);
        $view->assign('posts', $postsWithKeys);
        $view->assign('before', $before);
        $view->assign('after', $after);
        $view->assign('info', $info);
        $view->assign('subscription', $this->me->subscriptions()
            ->where('server', $origin)
            ->where('node', $node)
            ->first());
        $view->assign('paging', $this->_paging);

        $view->assign('publicposts', ($ids == false)
            ? \App\Post::where('server', $origin)
            ->where('node', $node)
            ->where('open', true)
            ->orderBy('published', 'desc')
            ->skip($page * $this->_paging)
            ->take($this->_paging)
            ->get()
            : false);

        $view->assign('first', $first);
        $view->assign('last', $last);
        $view->assign('count', $count);


        if ($first) {
            $view->assign('previouspage', $this->route(
                $node == AppPost::MICROBLOG_NODE ? 'contact' : 'community',
                [$origin, $node, $this->_beforeAfter . $first, $query]
            ));
        }

        $view->assign('nextpage', $this->route(
            $node == AppPost::MICROBLOG_NODE ? 'contact' : 'community',
            [$origin, $node, $last, $query]
        ));

        $html = $view->draw('_communityposts');

        return $html;
    }

    public function display()
    {
        $node = $this->get('n') != null ? $this->get('n') : AppPost::MICROBLOG_NODE;
        $this->view->assign('class', slugify('c' . $this->get('s') . '_' . $node));
    }
}
