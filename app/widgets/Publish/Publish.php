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
use Moxl\Xec\Action\Pubsub\TestPostPublish;
use Moxl\Xec\Action\Microblog\CommentCreateNode;
use \Michelf\Markdown;
use Respect\Validation\Validator;

class Publish extends WidgetBase
{
    function load()
    {
        $this->addjs('publish.js');
        $this->addcss('publish.css');
        $this->registerEvent('pubsub_postpublish_handle', 'onPublish');
        $this->registerEvent('pubsub_testpostpublish_handle', 'onTestPublish');
        $this->registerEvent('pubsub_testpostpublish_error', 'onTestPublishError');
    }

    function onPublish($packet)
    {
        list($to, $node, $id) = array_values($packet->content);

        RPC::call('Publish.enableSend');

        // Only for the microblog for the moment
        //if($node == 'urn:xmpp:microblog:0') {
            $this->ajaxCreateComments($to, $id);
        //}
    }

    function onTestPublish($packet)
    {
        list($server, $node) = array_values($packet->content);
        $this->ajaxCreate($server, $node);
    }

    function onTestPublishError($packet)
    {
        Notification::append(null, $this->__('publish.no_publication'));
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

    function ajaxCreateComments($server, $id)
    {
        if(!$this->validateServerNode($server, $id)) return;

        $cn = new CommentCreateNode;
        $cn->setTo($server)
           ->setParentId($id)
           ->request();
    }

    function ajaxFormFilled($server, $node)
    {
        $view = $this->tpl();

        $view->assign('server', $server);
        $view->assign('node', $node);

        Dialog::fill($view->draw('_publish_back_confirm', true));
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

    /*
     * Sic, doing this hack and wait to have a proper way to test it in the standard
     */
    function ajaxTestPublish($server, $node)
    {
        if(!$this->validateServerNode($server, $node)) return;

        $t = new TestPostPublish;
        $t->setTo($server)
          ->setNode($node)
          ->request();
    }

    function ajaxPublish($form)
    {
        RPC::call('Publish.disableSend');

        if($form->title->value != '') {
            $p = new PostPublish;
            $p->setFrom($this->user->getLogin())
              ->setTo($form->to->value)
              ->setTitle($form->title->value)
              ->setNode($form->node->value);
              //->setLocation($geo)
              //->enableComments()

            // Still usefull ? Check line 44
            if($form->node->value == 'urn:xmpp:microblog:0') {
                $p->enableComments();
            }

            $content = $content_xhtml = '';

            if($form->content->value != '') {
                $content = $form->content->value;
                $content_xhtml = Markdown::defaultTransform($content);
            }

            if($form->embed->value != '' && filter_var($form->embed->value, FILTER_VALIDATE_URL)) {
                try {
                    $embed = Embed\Embed::create($form->embed->value);
                    $p->setLink($form->embed->value);

                    if($embed->type == 'photo') {
                        $key = key($embed->images);
                        $p->setImage($embed->images[0]['value'], $embed->title, $embed->images[0]['mime']);
                    } else {
                        $content_xhtml .= $this->prepareEmbed($embed);
                    }
                } catch(Exception $e) {
                    error_log($e->getMessage());
                }
            }

            if($content != '') {
                $p->setContent($content);
            }

            if($content_xhtml != '') {
                $p->setContentXhtml(rawurldecode($content_xhtml));
            }

            $p->request();
        } else {
            RPC::call('Publish.enableSend');
            Notification::append(false, $this->__('publish.no_title'));
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

        try {
            $embed = Embed\Embed::create($url);
            $html = $this->prepareEmbed($embed);

            if($embed->type == 'photo') {
                RPC::call('movim_fill', 'gallery', $this->prepareGallery($embed));
                RPC::call('movim_fill', 'preview', '');
            } else {
                RPC::call('movim_fill', 'preview', $html);
                RPC::call('movim_fill', 'gallery', '');
            }
        } catch(Exception $e) {
            error_log($e->getMessage());
        }
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
