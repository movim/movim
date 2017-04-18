<?php

use Moxl\Xec\Action\Pubsub\PostDelete;
use Moxl\Xec\Action\Pubsub\Delete;

class PostActions extends \Movim\Widget\Base
{
    function load()
    {
        $this->registerEvent('pubsub_postdelete_handle', 'onDelete');
        $this->registerEvent('pubsub_postdelete', 'onDelete');
    }

    function onDelete($packet)
    {
        list($server, $node, $id) = array_values($packet->content);

        if(substr($node, 0, 29) == 'urn:xmpp:microblog:0:comments') {
            Notification::append(false, $this->__('post.comment_deleted'));

            $p = new Post;
            $p->ajaxGetComments($server, substr($node, 30));
        } else {
            Notification::append(false, $this->__('post.deleted'));

            if($node == 'urn:xmpp:microblog:0') {
                $this->rpc('MovimUtils.redirect', $this->route('news'));
            } else {
                $this->rpc('MovimUtils.redirect', $this->route('community', [$server, $node]));
            }
        }
    }

    function ajaxDelete($to, $node, $id)
    {
        $view = $this->tpl();

        $view->assign('to', $to);
        $view->assign('node', $node);
        $view->assign('id', $id);

        Dialog::fill($view->draw('_postactions_delete', true));
    }

    function ajaxDeleteConfirm($to, $node, $id)
    {
        $p = new PostDelete;
        $p->setTo($to)
          ->setNode($node)
          ->setId($id)
          ->request();

        $p = new Delete;
        $p->setTo($to)
          ->setNode('urn:xmpp:microblog:0:comments/'.$id)
          ->request();
    }


    function display()
    {
    }
}
