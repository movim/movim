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

use Moxl\Xec\Action\Microblog\CommentsGet;

class Post extends WidgetCommon
{
    function load()
    {
        $this->registerEvent('microblog_commentsget_handle', 'onComments');
    }

    function ajaxGetPost($id)
    {
        $html = $this->preparePost($id);
        $header = $this->prepareHeader($id);
        
        Header::fill($header);
        RPC::call('movim_fill', 'post_widget', $html);
    }

    function ajaxGetComments($jid, $id)
    {
        $c = new CommentsGet;
        $c->setTo($jid)
          ->setId($id)
          ->request();
    }

    function onComments($packet)
    {
        $nodeid = $packet->content;

        $p = new \Modl\ContactPostn();
        $p->nodeid = $nodeid;
        
        $pd = new \Modl\PostnDAO();
        $comments = $pd->getComments($p);

        $view = $this->tpl();
        $view->assign('comments', $comments);
        $html = $view->draw('_post_comments', true);
        //$html = $this->prepareComments($comments);
        RPC::call('movim_fill', 'comments', $html);
    }

    function prepareEmpty()
    {
        $view = $this->tpl();
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
            $view->assign('post', $p);
            $view->assign('attachements', $p->getAttachements());
            return $view->draw('_post', true);
        } else {
            return $this->prepareEmpty();
        }
    }

    function display()
    {
    }
}
