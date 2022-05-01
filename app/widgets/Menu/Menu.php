<?php

use Movim\Widget\Base;

include_once WIDGETS_PATH.'Post/Post.php';

use Illuminate\Database\Capsule\Manager as DB;

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

    public function onItem($packet)
    {
        $post = $packet->content;

        if ($post && $post->isComment()) {
            $post = $post->getParent();
        }

        if ($post) {
            $this->rpc('MovimTpl.fill', '#menu_widget #'.cleanupId($post->nodeid), $this->preparePost($post));
        }
    }

    public function onRetract($packet)
    {
        $this->ajaxHttpGetAll();
    }

    public function onStream($count)
    {
        $view = $this->tpl();
        $view->assign('count', $count);

        $this->rpc('MovimTpl.fill', '#menu_refresh', $view->draw('_menu_refresh'));
    }

    public function onPost($packet)
    {
        $since = \App\Cache::c('since');

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

        $post = $packet->content;

        if (!is_object($post)) {
            return;
        }

        $post = \App\Post::where('server', $post->server)
                         ->where('node', $post->node)
                         ->where('nodeid', $post->nodeid)
                         ->first();

        if ($post === null) {
            return;
        }

        if ($post->isComment()
        && !$post->isMine()) {
            $contact = \App\Contact::firstOrNew(['id' => $post->aid]);
            Notification::append(
                'comments',
                $contact->truename,
                ($post->isLike()) ? __('post.liked') : $post->title,
                $contact->getPhoto(),
                2
            );
        } elseif ($count > 0
        && (strtotime($post->published) > strtotime($since))) {
            if ($post->isMicroblog()) {
                $contact = \App\Contact::firstOrNew(['id' => $post->server]);

                $title = ($post->title == null)
                    ? __('post.default_title')
                    : $post->title;

                if (!$post->isMine()) {
                    Notification::append(
                        'news',
                        $contact->truename,
                        $title,
                        $contact->getPhoto(),
                        2,
                        $this->route('post', [$post->server, $post->node, $post->nodeid]),
                        $this->route('contact', $post->server)
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
                    $logo = $info->getPhoto('l');
                }

                Notification::append(
                    'news',
                    $title,
                    $post->title,
                    $logo,
                    2,
                    $this->route('post', [$post->server, $post->node, $post->nodeid]),
                    $this->route('community', [$post->server, $post->node])
                );
            }

            $this->onStream($count);
        }
    }

    public function ajaxHttpGetAll($page = 0)
    {
        $this->ajaxGet('all', null, null, $page);
    }

    public function ajaxHttpGetNews($page = 0)
    {
        $this->ajaxGet('news', null, null, $page);
    }

    public function ajaxHttpGetFeed($page = 0)
    {
        $this->ajaxGet('feed', null, null, $page);
    }

    public function ajaxGet($type = 'all', $server = null, $node = null, $page = 0)
    {
        $html = $this->prepareList($type, $server, $node, $page);

        $this->rpc('MovimTpl.fill', '#menu_widget', $html);
        $this->rpc('MovimUtils.enhanceArticlesContent');
    }

    public function prepareList($type = 'all', $server = null, $node = null, $page = 0)
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

        $since = \App\Cache::c('since');

        $count = ($since)
            ? $posts->where('published', '>', $since)->count()
            : 0;

        // getting newer, not older
        if ($page == 0) {
            $count = 0;
            $last = $posts->orderBy('published', 'desc')->first();
            \App\Cache::c('since', ($last) ? $last->published : date(MOVIM_SQL_DATE));
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

        $view->assign('previous', $this->route('news', $page-1));
        $view->assign('next', $this->route('news', $page+1));

        if ($type == 'news') {
            $view->assign('previous', $this->route('news', $page-1, [], 'communities'));
            $view->assign('next', $this->route('news', $page+1, [], 'communities'));
        } elseif ($type == 'feed') {
            $view->assign('previous', $this->route('news', $page-1, [], 'contacts'));
            $view->assign('next', $this->route('news', $page+1, [], 'contacts'));
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
        return (new \Post)->preparePost($post, false, true);
    }
}
