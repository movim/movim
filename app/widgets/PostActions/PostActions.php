<?php

use Movim\Widget\Base;

use Moxl\Xec\Action\Pubsub\PostDelete;
use Moxl\Xec\Action\Pubsub\Delete;

class PostActions extends Base
{
    public function load()
    {
        $this->registerEvent('pubsub_postdelete_handle', 'onDelete');
        $this->registerEvent('pubsub_postdelete', 'onDelete');
        $this->addjs('postactions.js');
    }

    public function onDelete($packet)
    {
        list($server, $node, $id) = array_values($packet->content);

        if (substr($node, 0, 29) == 'urn:xmpp:microblog:0:comments') {
            Toast::send($this->__('post.comment_deleted'));
        } else {
            Toast::send($this->__('post.deleted'));

            $this->rpc(
                'PostActions.handleDelete',
                ($node == 'urn:xmpp:microblog:0') ?
                $this->route('news') :
                $this->route('community', [$server, $node])
            );
        }

        $this->rpc('MovimTpl.remove', '#'.cleanupId($id));
    }

    public function ajaxLike($to, $node, $id)
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
            $view->assign('to', $to);
            $view->assign('node', $node);
            $view->assign('id', $id);

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
                  ->setNode('urn:xmpp:microblog:0:comments/'.$post->commentnodeid)
                  ->request();
            }
        }
    }

    public function preparePost($p)
    {
        $pw = new \Post;
        return $pw->preparePost($p, false, true);
    }
}
