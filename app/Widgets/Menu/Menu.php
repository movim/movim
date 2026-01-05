<?php

namespace App\Widgets\Menu;

use App\User;
use App\Post as AppPost;
use App\Widgets\ContactsSuggestions\ContactsSuggestions;
use App\Widgets\Notif\Notif;
use App\Widgets\Post\Post;
use Movim\Widget\Base;

use Illuminate\Database\Capsule\Manager as DB;
use Moxl\Xec\Payload\Packet;

class Menu extends Base
{
    private $_paging = 10;

    public function load()
    {
        $this->registerEvent('post', 'onPost');
        $this->registerEvent('post_retract', 'onRetract', 'news');
        $this->registerEvent('pubsubsubscription_add_handle', 'onSubscription', 'news');
        $this->registerEvent('pubsubsubscription_remove_handle', 'onSubscription', 'news');

        $this->addjs('menu.js');
        $this->addcss('menu.css');
    }

    public function onRetract(Packet $packet)
    {
        $this->rpc('MovimTpl.remove', '#' . cleanupId($packet->content['nodeid']));
    }

    public function onSubscription(Packet $packet)
    {
        $this->rpc('MovimTpl.fill', '#contacts_suggestions', $this->prepareContactsSuggestions());
    }

    public function onPost(Packet $packet)
    {
        $post = AppPost::find($packet->content);

        if (!$post) {
            return;
        }

        $since = $this->me->posts_since;

        if ($since) {
            $count = \App\Post::whereIn('id', function ($query) use ($since) {
                $filters = DB::table('posts')->where('id', -1);

                $filters = \App\Post::withMineScope($filters, $this->me, since: $since);
                $filters = \App\Post::withFollowScope($filters, $this->me, since: $since);

                $query->select('id')->from(
                    $filters,
                    'posts'
                );
            })->withoutComments()->count();
        } else {
            $count = 0;
        }

        if ($post->isEdited() && !$post->isComment()) {
            $this->rpc('MovimTpl.fill', '#menu_widget #' . cleanupId($post->nodeid), $this->preparePost($post));
            return;
        }

        if ($post->isComment() && !$post->isMine($this->me)) {
            $contact = \App\Contact::where('id', $post->aid)->first();
            $parent = $post->parent;

            if ($parent && $contact) {
                $this->notif(
                    key: 'comments',
                    title: ($post->isLike()) ? '❤️ ' . $contact->truename : $post->title,
                    body: '📝 ' . $parent->title,
                    url: $this->route('post', [$parent->server, $parent->node, $parent->nodeid]),
                    picture: $contact->getPicture(),
                    time: 4
                );
            }
        } elseif (
            !$post->isComment()
            && $count > 0
            && (strtotime($post->published) > strtotime($since))
        ) {
            if ($post->isMicroblog()) {
                $contact = \App\Contact::firstOrNew(['id' => $post->server]);

                if (!$post->isMine($this->me)) {
                    $this->notif(
                        key: 'news',
                        title: '📝 ' . $contact->truename,
                        body: $post->title,
                        url: $this->route('post', [$post->server, $post->node, $post->nodeid]),
                        picture: $contact->getPicture(),
                        time: 4,
                        actions: [[
                            'action' => 'reload',
                            'title' => $this->__('post.more'),
                        ]]
                    );
                }
            } else {
                $info = \App\Info::where('server', $post->server)
                    ->where('node', $post->node)
                    ->first();
                $logo = null;
                $title = $post->node;

                if ($info) {
                    if ($info->name) {
                        $title = $info->name;
                    }
                    $logo = $info->getPicture(\Movim\ImageSize::L);
                }

                $this->notif(
                    key: 'news',
                    title: $title,
                    body: $post->title,
                    url: $this->route('post', [$post->server, $post->node, $post->nodeid]),
                    picture: $logo,
                    time: 4,
                    actions: [[
                        'action' => 'reload',
                        'title' => $this->__('post.more'),
                    ]]
                );
            }

            $view = $this->tpl();
            $view->assign('count', $count);

            $this->rpc('MovimTpl.fill', '#menu_refresh', $view->draw('_menu_refresh'));
        }
    }

    public function ajaxHttpGetAll($page = 0)
    {
        $this->getList('all', $page);
        $this->rpc('MovimUtils.pushSoftState', $this->route('news'));
    }

    public function ajaxHttpGetCommunities($page = 0)
    {
        $this->getList('news', $page);
        $this->rpc('MovimUtils.pushSoftState', $this->route('news', false, [], 'communities'));
    }

    public function ajaxHttpGetContacts($page = 0)
    {
        $this->getList('feed', $page);
        $this->rpc('MovimUtils.pushSoftState', $this->route('news', false, [], 'contacts'));
    }

    private function getList($type = 'all', $page = 0)
    {
        $this->rpc('MovimTpl.fill', '#menu_widget', $this->prepareList($type, $page));
        $this->rpc('MovimUtils.enhanceArticlesContent');
    }

    public function prepareContactsSuggestions()
    {
        return (new ContactsSuggestions($this->me))->prepareContactsSuggestions();
    }

    public function prepareList($type = 'all', $page = 0)
    {
        $view = $this->tpl();

        $since = $this->me->posts_since;
        $posts = \App\Post::whereIn('id', function ($query) use ($since) {
            $filters = DB::table('posts')->where('id', -1);

            $filters = \App\Post::withMineScope($filters, $this->me, since: $since);
            $filters = \App\Post::withFollowScope($filters, $this->me, since: $since);

            $query->select('id')->from(
                $filters,
                'posts'
            );
        });

        $count = $since ? $posts->count() : 0;

        // getting newer, not older
        if ($page == 0) {
            $count = 0;
            $last = $posts->orderBy('published', 'desc')->first();
            $this->me->posts_since = ($last) ? $last->published : date(MOVIM_SQL_DATE);
            $this->me->save();
        }

        $items = \App\Post::skip($page * $this->_paging + $count)->withoutComments();

        $items->whereIn('id', function ($query) use ($type) {
            $filters = DB::table('posts')->where('id', -1);

            switch ($type) {
                case 'all':
                    $filters = \App\Post::withFollowScope($filters, $this->me);
                    $filters = \App\Post::withMineScope($filters, $this->me);
                    break;

                case 'feed':
                    $filters = \App\Post::withContactsFollowScope($filters, $this->me);
                    $filters = \App\Post::withMineScope($filters, $this->me);
                    break;

                case 'news':
                    $filters = \App\Post::withCommunitiesFollowScope($filters, $this->me);
                    break;
            }

            $query->select('id')->from(
                $filters,
                'posts'
            );
        });

        $view->assign('previous', $this->route('news', $page - 1));
        $view->assign('next', $this->route('news', $page + 1));

        if ($type == 'news') {
            $view->assign('previous', $this->route('news', $page - 1, [], 'communities'));
            $view->assign('next', $this->route('news', $page + 1, [], 'communities'));
        } elseif ($type == 'feed') {
            $view->assign('previous', $this->route('news', $page - 1, [], 'contacts'));
            $view->assign('next', $this->route('news', $page + 1, [], 'contacts'));
        }

        $items = $items
            ->orderBy('published', 'desc')
            ->take($this->_paging)->get();

        if ($items->isNotEmpty()) {
            $items = resolveInfos($items);
        }

        $view->assign('items', $items);
        $view->assign('type', $type);
        $view->assign('page', $page);
        $view->assign('paging', $this->_paging);

        return $view->draw('_menu_list');
    }

    public function preparePost($post)
    {
        return (new Post($this->me))->preparePost($post, false, true);
    }
}
