<?php

namespace App\Widgets\Blog;

use Movim\Widget\Base;
use App\Post;
use App\Widgets\Post\Post as PostWidget;

class Blog extends Base
{
    public $paging = 9;
    public $links = [];
    public $url;

    private $_from;
    private $_node;
    private $_item;
    private $_id;
    private $_contact;
    private $_posts = null;
    private int $_postsCount = 0;
    private int $_page = 0;
    private $_mode;
    private $_next;
    private $_tag;

    // Blog nickname
    private $_nickname = null;

    // Is gallery
    private $_gallery = false;

    public function load()
    {
        if ($this->_view == 'community') {
            $this->_from = $this->get('s');
            $this->_node = $this->get('n');

            if (!validateServerNode($this->_from, $this->_node)) {
                return;
            }

            $this->_item = \App\Info::where('server', $this->_from)
                                    ->where('node', $this->_node)
                                    ->first();
            $this->_mode = 'group';

            if ($this->_item) {
                $this->title = $this->_item->name ? $this->_item->name : $this->_node;
                $this->description = $this->_item->description;
            }

            $this->url = $this->route('community', [$this->_from, $this->_node]);

            $this->links[] = [
                'rel' => 'alternate',
                'type' => 'application/atom+xml',
                'href' => $this->route('feed', [$this->_from, $this->_node])
            ];

            if (!$this->get('i')) {
                $this->links[] = [
                    'rel' => 'alternate',
                    'type' => 'application/atom+xml',
                    'href' => 'xmpp:' . rawurlencode($this->_from) . '?;node=' . rawurlencode($this->_node)
                ];
            }
        } elseif ($this->_view == 'tag' && validateTag($this->get('t'))) {
            $this->_mode = 'tag';
            $this->_tag = strtolower(html_entity_decode($this->get('t')));
            $this->title = '#'.$this->_tag;
        } else {
            $this->_from = $this->get('f');

            $user = \App\User::where('nickname', $this->_from)->first();
            if ($user) {
                $this->_nickname = $this->_from;
                $this->_from = $user->id;
            }

            $this->_contact = \App\Contact::find($this->_from);

            if (filter_var($this->_from, FILTER_VALIDATE_EMAIL)) {
                $this->_node = Post::MICROBLOG_NODE;
            } else {
                return;
            }

            if ($this->_contact) {
                $this->title = __('blog.title', $this->_contact->truename);
                $this->description = $this->_contact->description;

                $avatar = $this->_contact->getPicture(\Movim\ImageSize::L);
                if ($avatar) {
                    $this->image = $avatar;
                }
            }

            $this->_mode = 'blog';

            $this->url = $this->route('blog', $this->_from);

            $this->links[] = [
                'rel' => 'alternate',
                'type' => 'application/atom+xml',
                'href' => $this->route('feed', [$this->_from])
            ];

            if (!$this->get('i')) {
                $this->links[] = [
                    'rel' => 'alternate',
                    'type' => 'application/atom+xml',
                    'href' => 'xmpp:' . rawurlencode($this->_from) . '?;node=' . rawurlencode($this->_node)
                ];
            }
        }

        $this->_postsCount = Post::where('server', $this->_from)
                    ->where('node', $this->_node)
                    ->where('open', true)
                    ->count();

        if ($this->_id = $this->get('i')) {
            $this->_posts = Post::where('server', $this->_from)
                    ->where('node', $this->_node)
                    ->where('nodeid', $this->_id)
                    ->where('open', true)
                    ->get();

            if ($this->_posts->isNotEmpty()) {
                $this->title = $this->_posts->first()->title;
                $this->description = !empty($this->_posts->first()->contentcleaned)
                    ? $this->_posts->first()->contentcleaned
                    : $this->_posts->first()->title;

                if ($this->_posts->first()->picture) {
                    $this->image = $this->_posts->first()->picture->href;
                }
            }

            if ($this->_view == 'community') {
                $this->url = $this->route('community', [$this->_from, $this->_node, $this->_id]);
            } else {
                $this->url = $this->route('blog', [$this->_from, $this->_id]);
            }

            $this->links[] = [
                'rel' => 'alternate',
                'type' => 'application/atom+xml',
                'href' => 'xmpp:'
                    . rawurlencode($this->_from)
                    . '?;node='
                    . rawurlencode($this->_node)
                    . ';item='
                    . rawurlencode($this->_id)
            ];
        } else {
            $this->_page = is_numeric($this->get('page')) ? (int)$this->get('page') : 0;

            if (isset($this->_tag)) {
                $tag = \App\Tag::where('name', $this->_tag)->first();
                if ($tag) {
                    $this->_posts = $tag->posts()
                         ->orderBy('published', 'desc')
                         ->take($this->paging + 1)
                         ->where('open', true)
                         ->skip($this->_page * $this->paging)->get();
                }
            } elseif ($this->_mode != 'blog' || ($this->_contact && $this->_contact->isPublic())) {
                $this->_posts = Post::where('server', $this->_from)
                        ->where('node', $this->_node)
                        ->where('open', true)
                        ->orderBy('published', 'desc')
                        ->skip($this->_page * $this->paging)
                        ->take($this->paging + 1)
                        ->get();
            }

            if ($this->_posts !== null) {
                $this->_gallery = $this->_item && $this->_item->isGallery();
            }
        }

        if ($this->_posts !== null
        && $this->_posts->count() == $this->paging + 1) {
            $this->_posts->pop();
            if ($this->_mode == 'blog') {
                $this->_next = $this->route('blog', $this->_from, ['page' => $this->_page + 1]);
            } elseif ($this->_mode == 'tag') {
                $this->_next = $this->route('tag', $this->_tag, ['page' => $this->_page + 1]);
            } else {
                $this->_next = $this->route('community', [$this->_from, $this->_node], ['page' => $this->_page + 1]);
            }
        }
    }

    public function preparePost(Post $post)
    {
        if ($this->_view == 'tag' && isLogged()) {
            return (new PostWidget)->preparePost($post, false, true);
        } else {
            $post->server = $this->_nickname ?? $post->server;
            return (new PostWidget)->preparePost($post, true);
        }
    }

    public function prepareTicket(Post $post)
    {
        return (new PostWidget)->prepareTicket($post, true);
    }

    public function display()
    {
        $this->view->assign('server', $this->_from);
        $this->view->assign('node', $this->_node);

        $this->view->assign('item', $this->_item);
        $this->view->assign('contact', $this->_contact);
        $this->view->assign('mode', $this->_mode);
        $this->view->assign('next', $this->_next);

        if ($this->_posts) {
            $this->_posts = resolveInfos($this->_posts);
        }
        $this->view->assign('posts', $this->_posts);
        $this->view->assign('postsCount', $this->_postsCount);
        $this->view->assign('gallery', $this->_gallery);

        $this->view->assign('tag', $this->_tag);
    }
}
