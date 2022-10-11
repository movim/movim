<?php

use Movim\Widget\Base;

include_once WIDGETS_PATH.'Post/Post.php';

class Blog extends Base
{
    public $_paging = 9;

    private $_from;
    private $_node;
    private $_item;
    private $_id;
    private $_contact;
    private $_posts = null;
    private $_page = 0;
    private $_mode;
    private $_next;
    private $_tag;

    // Blog nickname
    private $_nickname = null;

    // Is gallery
    private $_gallery = false;

    public function load()
    {
        $this->links = [];

        if ($this->_view == 'node') {
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

            $this->url = $this->route('node', [$this->_from, $this->_node]);

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
                $this->_node = 'urn:xmpp:microblog:0';
            } else {
                return;
            }

            if ($this->_contact) {
                $this->title = __('blog.title', $this->_contact->truename);
                $this->description = $this->_contact->description;

                $avatar = $this->_contact->getPhoto('l');
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

        if ($this->_id = $this->get('i')) {
            $this->_posts = \App\Post::where('server', $this->_from)
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

            if ($this->_view == 'node') {
                $this->url = $this->route('node', [$this->_from, $this->_node, $this->_id]);
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
            $this->_page = ($this->get('page')) ? $this->get('page') : 0;
            if (isset($this->_tag)) {
                $tag = \App\Tag::where('name', $this->_tag)->first();
                if ($tag) {
                    $this->_posts = $tag->posts()
                         ->orderBy('published', 'desc')
                         ->take($this->_paging + 1)
                         ->where('open', true)
                         ->skip($this->_page * $this->_paging)->get();
                }
            } else {
                $this->_posts = \App\Post::where('server', $this->_from)
                        ->where('node', $this->_node)
                        ->where('open', true)
                        ->orderBy('published', 'desc')
                        ->skip($this->_page * $this->_paging)
                        ->take($this->_paging + 1)
                        ->get();
            }

            if ($this->_posts !== null) {
                $this->_gallery = isPostGallery($this->_posts);
            }
        }

        if ($this->_posts !== null
        && $this->_posts->count() == $this->_paging + 1) {
            $this->_posts->pop();
            if ($this->_mode == 'blog') {
                $this->_next = $this->route('blog', $this->_from, ['page' => $this->_page + 1]);
            } elseif ($this->_mode == 'tag') {
                $this->_next = $this->route('tag', $this->_tag, ['page' => $this->_page + 1]);
            } else {
                $this->_next = $this->route('node', [$this->_from, $this->_node], ['page' => $this->_page + 1]);
            }
        }
    }

    public function preparePost(\App\Post $post)
    {
        if ($this->_view == 'tag' && isLogged()) {
            return (new Post)->preparePost($post, false, true);
        } else {
            $post->server = $this->_nickname ?? $post->server;
            return (new Post)->preparePost($post, true);
        }
    }

    public function prepareTicket(\App\Post $post)
    {
        return (new \Post)->prepareTicket($post);
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
        $this->view->assign('gallery', $this->_gallery);

        $this->view->assign('tag', $this->_tag);
    }
}
