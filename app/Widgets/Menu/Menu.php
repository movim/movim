<?php

namespace App\Widgets\Menu;

use App\User;
use App\Post as AppPost;
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
        $this->registerEvent('pubsub_postdelete', 'onRetract', 'news');
        $this->registerEvent('pubsub_getitem_handle', 'onItem', 'news');

        $this->addjs('menu.js');
        $this->addcss('menu.css');
    }

    public function onItem(Packet $packet)
    {
        $post = AppPost::find($packet->content);

        if ($post) {
            if ($post->isComment()) {
                $post = $post->getParent();
            }

            $this->rpc('MovimTpl.fill', '#menu_widget #' . cleanupId($post->nodeid), $this->preparePost($post));
        }
    }

    public function onRetract(Packet $packet)
    {
        $this->ajaxHttpGetAll();
    }

    public function onPost(Packet $packet)
    {
        $since = User::me(true)->posts_since; // Force refresh the user

        if ($since) {
            $count = \App\Post::whereIn('id', function ($query) {
                $filters = DB::table('posts')->where('id', -1);

                $filters = \App\Post::withMineScope($filters);
                $filters = \App\Post::withContactsScope($filters);
                $filters = \App\Post::withSubscriptionsScope($filters);

                $query->select('id')->from(
                    $filters,
                    'posts'
                );
            })->withoutComments()->where('published', '>', $since)->count();
        } else {
            $count = 0;
        }

        $post = AppPost::find($packet->content);

        if (!is_object($post)) {
            return;
        }

        $post = \App\Post::where('server', $post->server)
            ->where('node', $post->node)
            ->where('nodeid', $post->nodeid)
            ->first();

        if ($post === null || $post->isEdited()) {
            return;
        }

        if ($post->isComment() && !$post->isMine($this->me)) {
            $contact = \App\Contact::where('id', $post->aid)->first();
            $parent = $post->parent;

            if ($parent && $contact) {
                Notif::append(
                    'comments',
                    ($post->isLike()) ? '❤️ ' . $contact->truename : $post->title,
                    '📝 ' . $parent->title,
                    $contact->getPicture(),
                    4
                );
            }
        } elseif (
            !$post->isComment()
            && $count > 0
            && (strtotime($post->published) > strtotime($since))
        ) {
            if ($post->isMicroblog() || $post->isStory()) {
                $contact = \App\Contact::firstOrNew(['id' => $post->server]);

                if (!$post->isMine($this->me)) {
                    Notif::append(
                        'news',
                        '📝 ' . ($post->isStory() ? __('stories.new_story', $contact->truename) : $contact->truename),
                        $post->title,
                        $contact->getPicture(),
                        4,
                        $post->isStory() ? $this->route('chat') : $this->route('post', [$post->server, $post->node, $post->nodeid]),
                        $post->isStory() ? null : $this->route('contact', $post->server)
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

                Notif::append(
                    'news',
                    $title,
                    $post->title,
                    $logo,
                    4,
                    $this->route('post', [$post->server, $post->node, $post->nodeid]),
                    $this->route('community', [$post->server, $post->node])
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

    public function prepareList($type = 'all', $page = 0)
    {
        $view = $this->tpl();

        $posts = \App\Post::whereIn('id', function ($query) {
            $filters = DB::table('posts')->where('id', -1);

            $filters = \App\Post::withMineScope($filters);
            $filters = \App\Post::withContactsScope($filters);
            $filters = \App\Post::withSubscriptionsScope($filters);

            $query->select('id')->from(
                $filters,
                'posts'
            );
        });

        $since = $this->me->posts_since;

        $count = ($since)
            ? $posts->where('published', '>', $since)->count()
            : 0;

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

            if (in_array($type, ['all', 'feed'])) {
                $filters = \App\Post::withContactsScope($filters);
                $filters = \App\Post::withMineScope($filters);
            }

            if (in_array($type, ['all', 'news'])) {
                $filters = \App\Post::withSubscriptionsScope($filters);
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
        return (new Post())->preparePost($post, false, true);
    }
}
