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
use Moxl\Xec\Action\Microblog\CommentsGet;
use \Michelf\Markdown;

class Post extends WidgetCommon
{
    function load()
    {
        $this->addcss('post.css');
        $this->registerEvent('microblog_commentsget_handle', 'onComments');
    }

    function ajaxClear()
    {
        RPC::call('movim_fill', 'post_widget', $this->prepareEmpty());
    }

    function ajaxGetPost($id)
    {
        $html = $this->preparePost($id);
        $header = $this->prepareHeader($id);
        
        Header::fill($header);
        RPC::call('movim_fill', 'post_widget', $html);
    }

    function ajaxCreate()
    {
        $view = $this->tpl();
        $view->assign('to', $this->user->getLogin());
        RPC::call('movim_fill', 'post_widget', $view->draw('_post_create', true));

        $view = $this->tpl();
        Header::fill($view->draw('_post_header_create', true));
    }

    function ajaxPreview($form)
    {
        if($form->content->value != '') {
            $view = $this->tpl();
            $view->assign('content', Markdown::defaultTransform($form->content->value));

            Dialog::fill($view->draw('_post_preview', true));
        } else {
            Notification::append(false, $this->__('post.no_content_preview'));
        }
    }

    function ajaxHelp()
    {
        $view = $this->tpl();
        Dialog::fill($view->draw('_post_help', true), true);
    }

    function ajaxPublish($form)
    {
        if($form->content->value != '') {
            $content = Markdown::defaultTransform($form->content->value);

            if($form->embed->value != '' && filter_var($form->embed->value, FILTER_VALIDATE_URL)) {
                $content .= $this->prepareEmbed($form->embed->value);
            }
            
            $p = new PostPublish;
            $p->setFrom($this->user->getLogin())
              ->setTo($form->to->value)
              ->setNode($form->node->value);
              //->setLocation($geo)
              //->enableComments()
            if($form->title->value != '') {
                $p->setTitle($form->title->value);
            }
            $p->setContentHtml(rawurldecode($content))
              ->request();
        } else {
            Notification::append(false, $this->__('post.no_content'));
        }
    }

    function ajaxGetComments($jid, $id)
    {
        $c = new CommentsGet;
        $c->setTo($jid)
          ->setId($id)
          ->request();
    }

    function ajaxEmbedTest($url)
    {
        if($url == '') {
            return;
        } elseif(!filter_var($url, FILTER_VALIDATE_URL)) {
            Notification::append(false, $this->__('post.valid_url'));
            return;
        }

        $html = $this->prepareEmbed($url);

        RPC::call('movim_fill', 'preview', $html);
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

    function prepareEmbed($url)
    {
        $info = Embed\Embed::create($url);

        $view = $this->tpl();
        $view->assign('embed', $info);
        return $view->draw('_post_embed', true);
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
