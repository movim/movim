<?php

use Movim\Widget\Base;

include_once WIDGETS_PATH.'Post/Post.php';

class Menu extends Base
{
    private $_paging = 10;

    public function load()
    {
        $this->registerEvent('post', 'onPost');
        $this->registerEvent('post_retract', 'onRetract', 'news');
        $this->registerEvent('pubsub_postdelete', 'onRetract', 'news');

        $this->addjs('menu.js');
    }

    public function onRetract($packet)
    {
        $this->ajaxGetAll();
    }

    public function onStream($count)
    {
        $view = $this->tpl();
        $view->assign('count', $count);
        $view->assign('refresh', $this->call('ajaxGetAll'));

        $this->rpc('movim_posts_unread', $count);
        $this->rpc('MovimTpl.fill', '#menu_refresh', $view->draw('_menu_refresh'));
    }

    public function onPost($packet)
    {
        $since = \App\Cache::c('since');

        if ($since) {
            $count = \App\Post::whereIn('id', function ($query) {
                $query = $query->select('id')->from('posts');
                $query = \App\Post::withContactsScope($query);
                $query = \App\Post::withMineScope($query);
                $query = \App\Post::withSubscriptionsScope($query);
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
                $logo = ($post->logo) ? $post->getLogo() : null;

                Notification::append(
                    'news',
                    $post->title,
                    $post->node,
                    $logo,
                    2,
                    $this->route('post', [$post->server, $post->node, $post->nodeid]),
                    $this->route('community', [$post->server, $post->node])
                );
            }

            $this->onStream($count);
        }
    }

    public function ajaxGetAll($page = 0)
    {
        $this->ajaxGet('all', null, null, $page);
    }

    public function ajaxGetNews($page = 0)
    {
        $this->ajaxGet('news', null, null, $page);
    }

    public function ajaxGetFeed($page = 0)
    {
        $this->ajaxGet('feed', null, null, $page);
    }

    public function ajaxGetNode($server = null, $node = null, $page = 0)
    {
        $this->ajaxGet('node', $server, $node, $page);
    }

    public function ajaxGetMe($page = 0)
    {
        $this->ajaxGet('me', null, null, $page);
    }

    public function ajaxGet($type = 'all', $server = null, $node = null, $page = 0)
    {
        $html = $this->prepareList($type, $server, $node, $page);

        $this->rpc('MovimTpl.fill', '#menu_widget', $html);
        $this->rpc('MovimUtils.enhanceArticlesContent');
        $this->rpc('Menu.refresh');
    }

    public function prepareList($type = 'all', $server = null, $node = null, $page = 0)
    {
        $view = $this->tpl();

        $posts = \App\Post::whereIn('id', function ($query) {
            $query = $query->select('id')->from('posts');
            $query = \App\Post::withContactsScope($query);
            $query = \App\Post::withMineScope($query);
            $query = \App\Post::withSubscriptionsScope($query);
        });

        $since = \App\Cache::c('since');

        $count = ($since)
            ? $posts->where('published', '>', $since)->count()
            : 0;

        // getting newer, not older
        if ($page == 0) {
            $count = 0;
            $last = $posts->orderBy('published', 'desc')->first();
            \App\Cache::c('since', ($last) ? $last->published : date(SQL_DATE));
        }

        $items = \App\Post::skip($page * $this->_paging + $count)->withoutComments();

        $items->whereIn('id', function ($query) use ($type) {
            $query = $query->select('id')->from('posts');

            if (in_array($type, ['all', 'feed'])) {
                $query = \App\Post::withContactsScope($query);
                $query = \App\Post::withMineScope($query);
            }

            if (in_array($type, ['all', 'news'])) {
                $query = \App\Post::withSubscriptionsScope($query);
            }
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

        $view->assign('items', $items
            ->orderBy('published', 'desc')
            ->take($this->_paging)->get());
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
