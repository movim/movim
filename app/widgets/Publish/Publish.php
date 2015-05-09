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
use Moxl\Xec\Action\Microblog\CommentCreateNode;
use \Michelf\Markdown;
use Respect\Validation\Validator;

class Publish extends WidgetCommon
{
    function load()
    {
        $this->addjs('publish.js');
        $this->registerEvent('pubsub_publishpublish_handle', 'onPublish');
    }

    function onPublish($packet)
    {
        list($to, $node, $id) = array_values($packet->content);

        // Only for the microblog for the moment
        if($node == 'urn:xmpp:microblog:0') {
            $cn = new CommentCreateNode;
            $cn->setTo($to)
               ->setParentId($id)
               ->request();
        }
    }

    function ajaxCreateBlog()
    {
        $this->ajaxCreate($this->user->getLogin(), 'urn:xmpp:microblog:0');
    }

    function ajaxCreate($server, $node)
    {
        if(!$this->validateServerNode($server, $node)) return;

        $view = $this->tpl();
        $view->assign('to', $server);
        $view->assign('node', $node);
        RPC::call('MovimTpl.fill', 'main section > div:nth-child(2)', $view->draw('_publish_create', true));

        $pd = new \Modl\ItemDAO;
        $item = $pd->getItem($server, $node);

        $view = $this->tpl();
        $view->assign('item', $item);
        $view->assign('server', $server);
        $view->assign('node', $node);
        Header::fill($view->draw('_publish_header', true));
        
        RPC::call('Publish.setEmbed');
    }

    function ajaxPreview($form)
    {
        if($form->content->value != '') {
            $view = $this->tpl();
            $view->assign('content', Markdown::defaultTransform($form->content->value));

            Dialog::fill($view->draw('_publish_preview', true), true);
        } else {
            Notification::append(false, $this->__('publish.no_content_preview'));
        }
    }

    function ajaxHelp()
    {
        $view = $this->tpl();
        Dialog::fill($view->draw('_publish_help', true), true);
    }

    function ajaxPublish($form)
    {
        if($form->content->value != '') {
            $content = Markdown::defaultTransform($form->content->value);

            $p = new PostPublish;
            $p->setFrom($this->user->getLogin())
              ->setTo($form->to->value)
              ->setNode($form->node->value);
              //->setLocation($geo)
              //->enableComments()
            if($form->title->value != '') {
                $p->setTitle($form->title->value);
            }

            if($form->embed->value != '' && filter_var($form->embed->value, FILTER_VALIDATE_URL)) {
                $embed = Embed\Embed::create($form->embed->value);
                $content .= $this->prepareEmbed($embed);
                $p->setLink($form->embed->value);

                if($embed->type == 'photo') {
                    $key = key($embed->images);
                    $p->setImage($embed->images[0]['value'], $embed->title, $embed->images[0]['mime']);
                }
            }

            $p->setContentHtml(rawurldecode($content))
              ->request();
        } else {
            Notification::append(false, $this->__('publish.no_content'));
        }
    }

    function ajaxEmbedTest($url)
    {
        if($url == '') {
            return;
        } elseif(!filter_var($url, FILTER_VALIDATE_URL)) {
            Notification::append(false, $this->__('publish.valid_url'));
            return;
        }

        $embed = Embed\Embed::create($url);
        $html = $this->prepareEmbed($embed);

        if($embed->type == 'photo') {
            RPC::call('movim_fill', 'gallery', $this->prepareGallery($embed));
        }

        RPC::call('movim_fill', 'preview', $html);
    }

    function prepareEmbed($embed)
    {
        $view = $this->tpl();
        $view->assign('embed', $embed);
        return $view->draw('_publish_embed', true);
    }

    function prepareGallery($embed)
    {
        $view = $this->tpl();
        $view->assign('embed', $embed);
        return $view->draw('_publish_gallery', true);
    }

    private function validateServerNode($server, $node)
    {
        $validate_server = Validator::string()->noWhitespace()->length(6, 40);
        $validate_node = Validator::string()->length(3, 100);

        if(!$validate_server->validate($server)
        || !$validate_node->validate($node)
        ) return false;
        else return true;
    }

    function display()
    {
    }
}
