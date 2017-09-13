<?php

use Moxl\Xec\Action\Pubsub\PostDelete;
use Moxl\Xec\Action\Pubsub\Delete;

class PostActions extends \Movim\Widget\Base
{
    function load()
    {
        $this->registerEvent('pubsub_getitem_handle', 'onItem');
        $this->registerEvent('pubsub_postdelete_handle', 'onDelete');
        $this->registerEvent('pubsub_postdelete', 'onDelete');
        $this->addjs('postactions.js');
    }

    function onItem($packet)
    {
        list($origin, $node, $id) = array_values($packet->content);

        $pd = new \Modl\PostnDAO;
        $p = $pd->get($origin, $node, $id);

        if($p && $p->isComment()) $p = $p->getParent();

        if($p) {
            $this->rpc('MovimTpl.fill', '#'.cleanupId($p->nodeid), $this->preparePost($p));
        }
    }

    function onDelete($packet)
    {
        list($server, $node, $id) = array_values($packet->content);

        if(substr($node, 0, 29) == 'urn:xmpp:microblog:0:comments') {
            Notification::append(false, $this->__('post.comment_deleted'));
        } else {
            Notification::append(false, $this->__('post.deleted'));

            $this->rpc('PostActions.handleDelete',
                ($node == 'urn:xmpp:microblog:0') ?
                $this->route('news') :
                $this->route('community', [$server, $node])
            );
        }

        $this->rpc('MovimTpl.remove', '#'.cleanupId($id));
    }

    function ajaxLike($to, $node, $id)
    {
        $pd = new \Modl\PostnDAO;
        $p = $pd->get($to, $node, $id);
        if(!isset($p) || $p->isLiked()) return;

        $post = new Post;
        $post->publishComment('♥', $p->origin, $p->node, $p->nodeid);
    }

    function ajaxDelete($to, $node, $id)
    {
        $view = $this->tpl();

        $pd = new \Modl\PostnDAO;
        $p = $pd->get($to, $node, $id);

        if(isset($p)) {
            $view->assign('post', $p);
            $view->assign('to', $to);
            $view->assign('node', $node);
            $view->assign('id', $id);

            Dialog::fill($view->draw('_postactions_delete', true));
        }
    }

    function ajaxDeleteConfirm($to, $node, $id)
    {
        $pd = new \Modl\PostnDAO;
        $post = $pd->get($to, $node, $id);

        if(isset($post)) {
            $p = new PostDelete;
            $p->setTo($post->origin)
              ->setNode($post->node)
              ->setId($post->nodeid)
              ->request();

            $p = new Delete;
            $p->setTo($post->commentorigin)
              ->setNode('urn:xmpp:microblog:0:comments/'.$post->commentnodeid)
              ->request();
        }
    }

    public function preparePost($p)
    {
        $pw = new \Post;
        return $pw->preparePost($p, true, false, true);
    }
}
