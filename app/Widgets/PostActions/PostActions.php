<?php

namespace App\Widgets\PostActions;

use App\Post as AppPost;
use App\Widgets\Dialog\Dialog;
use App\Widgets\Post\Post;
use App\Widgets\Toast\Toast;
use Movim\Widget\Base;

use Moxl\Xec\Action\Pubsub\PostDelete;
use Moxl\Xec\Action\Pubsub\Delete;
use Moxl\Xec\Payload\Packet;

class PostActions extends Base
{
    public function load()
    {
        $this->registerEvent('pubsub_postdelete_handle', 'onDelete');
        $this->registerEvent('pubsub_postdelete', 'onDelete');
        $this->addjs('postactions.js');
    }

    public function onDelete(Packet $packet)
    {
        list($server, $node, $id) = array_values($packet->content);

        if (str_starts_with($node, AppPost::COMMENTS_NODE)) {
            Toast::send($this->__('post.comment_deleted'));
        } else {
            Toast::send($this->__('post.deleted'));

            $this->rpc(
                'PostActions.handleDelete',
                ($node == AppPost::MICROBLOG_NODE) ?
                    $this->route('news') :
                    $this->route('community', [$server, $node])
            );
        }

        $this->rpc('MovimTpl.remove', '#' . cleanupId($id));
    }

    public function ajaxLike(string $to, string $node, string $id)
    {
        $p = \App\Post::where('server', $to)
            ->where('node', $node)
            ->where('nodeid', $id)
            ->first();

        if (!isset($p) || $p->isLiked()) {
            return;
        }

        $post = new Post;
        $post->publishComment('♥', $p->server, $p->node, $p->nodeid);
    }

    public function ajaxDelete($to, $node, $id)
    {
        $post = \App\Post::where('server', $to)
            ->where('node', $node)
            ->where('nodeid', $id)
            ->first();

        if ($post) {
            $view = $this->tpl();

            $view->assign('post', $post);

            Dialog::fill($view->draw('_postactions_delete'));
        }
    }

    public function ajaxDeleteConfirm($to, $node, $id)
    {
        $post = \App\Post::where('server', $to)
            ->where('node', $node)
            ->where('nodeid', $id)
            ->first();

        if (isset($post)) {
            $p = new PostDelete;
            $p->setTo($post->server)
                ->setNode($post->node)
                ->setId($post->nodeid)
                ->request();

            if (!$post->isComment()) {
                $p = new Delete;
                $p->setTo($post->commentserver)
                    ->setNode(AppPost::COMMENTS_NODE . '/' . $post->commentnodeid)
                    ->request();
            }
        }
    }

    public function preparePost($p)
    {
        return (new \App\Post)->preparePost($p, false, true);
    }
}
