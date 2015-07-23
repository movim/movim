<?php

/**
 * @package Widgets
 *
 * @file Post.php
 * This file is part of Movim.
 *
 * @brief The Post visualisation widget
 *
 * @author Jaussoin Timothée <edhelas_at_movim_dot_com>
 *
 * @version 1.0
 * @date 1 december 2014
 *
 * Copyright (C)2014 MOVIM project
 *
 * See COPYING for licensing information.
 */

use Moxl\Xec\Action\Pubsub\PostPublish;
use Moxl\Xec\Action\Pubsub\PostDelete;
use Moxl\Xec\Action\Microblog\CommentsGet;
use Moxl\Xec\Action\Microblog\CommentCreateNode;
use Moxl\Xec\Action\Microblog\CommentPublish;
use \Michelf\Markdown;
use Respect\Validation\Validator;

class Post extends WidgetBase
{
    function load()
    {
        $this->addcss('post.css');
        $this->addjs('post.js');
        $this->registerEvent('microblog_commentsget_handle', 'onComments');
        $this->registerEvent('microblog_commentpublish_handle', 'onCommentPublished');
        $this->registerEvent('microblog_commentsget_error', 'onCommentsError');
        $this->registerEvent('pubsub_postpublish_handle', 'onPublish');
        $this->registerEvent('pubsub_postdelete_handle', 'onDelete');
    }

    function onPublish($packet)
    {
        Notification::append(false, $this->__('post.published'));
        $this->ajaxClear();
        RPC::call('MovimTpl.hidePanel');
    }

    function onCommentPublished($packet)
    {
        Notification::append(false, $this->__('post.comment_published'));
        $this->onComments($packet);
    }

    function onDelete()
    {
        Notification::append(false, $this->__('post.deleted'));
        $this->ajaxClear();
        RPC::call('MovimTpl.hidePanel');
        RPC::call('Menu_ajaxGetAll');
    }

    function onComments($packet)
    {
        list($server, $node, $id) = array_values($packet->content);

        $p = new \Modl\ContactPostn();
        $p->nodeid = $id;

        $pd = new \Modl\PostnDAO();
        $comments = $pd->getComments($p);

        $view = $this->tpl();
        $view->assign('comments', $comments);
        $view->assign('server', $server);
        $view->assign('node', $node);
        $view->assign('id', $id);

        $html = $view->draw('_post_comments', true);
        RPC::call('movim_fill', 'comments', $html);
    }

    function onCommentsError($packet)
    {
        $view = $this->tpl();
        $html = $view->draw('_post_comments_error', true);
        RPC::call('movim_fill', 'comments', $html);
    }

    function ajaxClear()
    {
        RPC::call('movim_fill', 'post_widget', $this->prepareEmpty());
        RPC::call('Menu.refresh');
        //RPC::call('Menu_ajaxGetAll');
    }

    function ajaxGetPost($id)
    {
        $html = $this->preparePost($id);
        $header = $this->prepareHeader($id);

        Header::fill($header);
        RPC::call('movim_fill', 'post_widget', $html);
    }

    function ajaxDelete($to, $node, $id)
    {
        $view = $this->tpl();

        $view->assign('to', $to);
        $view->assign('node', $node);
        $view->assign('id', $id);

        Dialog::fill($view->draw('_post_delete', true));
    }

    function ajaxDeleteConfirm($to, $node, $id) {
        $p = new PostDelete;
        $p->setTo($to)
          ->setNode($node)
          ->setId($id)
          ->request();
    }

    function ajaxGetComments($jid, $id)
    {
        $pd = new \Modl\PostnDAO();
        $pd->deleteNode($jid, "urn:xmpp:microblog:0:comments/".$id);

        $c = new CommentsGet;
        $c->setTo($jid)
          ->setId($id)
          ->request();
    }

    function ajaxPublishComment($form, $to, $node, $id)
    {
        $comment = trim($form->comment->value);

        $validate_comment = Validator::string()->notEmpty();
        $validate_id = Validator::string()->length(6, 128)->noWhitespace();

        if(!$validate_comment->validate($comment)
        || !$validate_id->validate($id)) return;

        $cp = new CommentPublish;
        $cp->setTo($to)
           ->setFrom($this->user->getLogin())
           ->setParentId($id)
           ->setContent(htmlspecialchars(rawurldecode($comment)))
           ->request();
    }

    function prepareEmpty()
    {
        $view = $this->tpl();

        $nd = new \modl\PostnDAO();
        $view = $this->tpl();
        $view->assign('posts', $nd->getLastPublished(0, 8));

        return $view->draw('_post_empty', true);
    }

    function prepareHeader($id)
    {
        $pd = new \Modl\PostnDAO;
        $p  = $pd->getItem($id);

        $view = $this->tpl();

        if(isset($p)) {
            $view->assign('post', $p);
        } else {
            $view->assign('post', null);
        }

        return $view->draw('_post_header', true);
    }

    function preparePost($id)
    {
        $pd = new \Modl\PostnDAO;
        $p  = $pd->getItem($id);

        $view = $this->tpl();

        if(isset($p)) {
            if(isset($p->commentplace)) {
                $this->ajaxGetComments($p->commentplace, $p->nodeid);
            }

            $view->assign('recycled', false);

            // Is it a recycled post ?
            if($p->getContact()->jid
            && $p->node == 'urn:xmpp:microblog:0'
            && ($p->origin != $p->getContact()->jid)) {
                $cd = new \Modl\ContactDAO;
                $view->assign('recycled', $cd->get($p->origin));
            }

            $view->assign('post', $p);
            $view->assign('attachements', $p->getAttachements());
            return $view->draw('_post', true);
        } else {
            return $this->prepareEmpty();
        }
    }

    function ajaxTogglePrivacy($id) {
        $validate = Validator::string()->length(6, 128);

        if(!$validate->validate($id))
            return;

        $pd = new \Modl\PrivacyDAO();
        $p = $pd->get($id);

        $pd = new \Modl\PostnDAO;
        $po  = $pd->getItem($id);

        if($po->privacy == 1) {
            Notification::append(false, $this->__('post.blog_remove'));
            \Modl\Privacy::set($id, 0);
        } if($po->privacy == 0) {
            Notification::append(false, $this->__('post.blog_add'));
            \Modl\Privacy::set($id, 1);
        }
    }

    function display()
    {
        $validate_nodeid = Validator::string()->length(10, 100);

        $this->view->assign('nodeid', false);
        if($validate_nodeid->validate($this->get('n'))) {
            $this->view->assign('nodeid', $this->get('n'));
        }
    }
}
