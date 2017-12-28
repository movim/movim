<?php

use Moxl\Xec\Action\Pubsub\PostPublish;

use Movim\Session;
use Movim\Cache;

use Respect\Validation\Validator;

class PublishBrief extends \Movim\Widget\Base
{
    function load()
    {
        $this->registerEvent('pubsub_postpublish_handle', 'onPublish');

        $this->addjs('publishbrief.js');
        $this->addcss('publishbrief.css');
    }

    function onPublish($packet)
    {
        Notification::append(false, $this->__('post.published'));

        list($to, $node, $id, $repost, $comments) = array_values($packet->content);

        if(!$repost && $comments) {
            $p = new Publish;
            $p->ajaxCreateComments(($comments === true) ? $to : $comments, $id);
        }
    }

    function ajaxGet()
    {
        $this->rpc('MovimTpl.fill', '#publishbrief', $this->preparePublishBrief());
        $this->rpc('PublishBrief.checkEmbed');
    }

    function ajaxSaveDraft($form)
    {
        $p = new \Modl\Postn;
        $p->title = $form->title->value;

        if(Validator::notEmpty()->url()->validate($form->embed->value)) {
            array_push($p->links, $form->embed->value);
        }

        Cache::c('draft', $p);
    }

    function ajaxPublish($form)
    {
        $this->rpc('PublishBrief.disableSend');

        Cache::c('draft', null);

        if(Validator::stringType()->notEmpty()->validate(trim($form->title->value))) {
            $p = new PostPublish;
            $p->setFrom($this->user->getLogin())
              ->setTo($this->user->getLogin())
              ->setTitle(htmlspecialchars($form->title->value))
              ->setNode('urn:xmpp:microblog:0');

            $cd = new \Modl\CapsDAO;
            $comments = $cd->getComments($this->user->getServer());

            if($comments) {
                $p->enableComments($comments->node);
            } else {
                $p->enableComments();
            }

            if($form->open->value === true) {
                $p->isOpen();
            }

            $tags = getHashtags(htmlspecialchars($form->title->value));
            if(is_array($tags)) {
                $p->setTags($tags);
            }

            if(Validator::notEmpty()->url()->validate($form->embed->value)) {
                try {
                    $murl = new \Modl\Url;
                    $embed = $murl->resolve($form->embed->value);
                    $p->setLink($form->embed->value);

                    if($embed->type == 'photo' || isset($embed->images)) {
                        $p->setImage($embed->images[0]['url'],
                                     $embed->title,
                                     $embed->images[0]['mime']);
                    }

                    $p->setLink($form->embed->value,
                                $embed->title,
                                'text/html',
                                $embed->description,
                                $embed->providerIcon);
                } catch(Exception $e) {
                    error_log($e->getMessage());
                }
            }

            $p->request();
            $this->ajaxGet();
        } else {
            $this->rpc('PublishBrief.enableSend');
        }
    }

    function ajaxEmbedLoading()
    {
        $this->rpc('MovimTpl.fill', '#publishbrief p.embed', $this->__('global.loading'));
    }

    function ajaxEmbedTest($url)
    {
        if($url == '') {
            return;
        }

        if(!Validator::url()->validate($url)) {
            Notification::append(false, $this->__('publish.valid_url'));
            $this->ajaxClearEmbed();
            return;
        }

        $this->rpc('Dialog_ajaxClear');

        try {
            $murl = new \Modl\Url;
            $embed = $murl->resolve($url);
            $this->rpc('MovimTpl.fill', '#publishbrief p.embed', $this->prepareEmbed($embed));
            if ($embed->type == 'link') {
                $this->rpc('PublishBrief.setTitle', $embed->title);
            }
        } catch(Exception $e) {
            error_log($e->getMessage());
        }
    }

    function ajaxClearEmbed()
    {
        $session = Session::start();
        $session->remove('share_url');
        $this->rpc('MovimTpl.fill', '#publishbrief p.embed', $this->prepareEmbedDefault());
    }

    function prepareEmbedDefault()
    {
        $view = $this->tpl();
        return $view->draw('_publishbrief_embed_default', true);
    }

    function prepareEmbed($embed)
    {
        $view = $this->tpl();
        $view->assign('embed', $embed);
        return $view->draw('_publishbrief_embed', true);
    }

    function preparePublishBrief()
    {
        $view = $this->tpl();

        $session = Session::start();
        $view->assign('url', $session->get('share_url'));
        $view->assign('draft', Cache::c('draft'));
        $view->assign('embed', $this->prepareEmbedDefault());
        return $view->draw('_publishbrief', true);
    }

    function ajaxLink()
    {
        $view = $this->tpl();
        Dialog::fill($view->draw('_publishbrief_link', true));
    }

    function ajaxDisplayPrivacy($open)
    {
        Notification::append(false, ($open)
            ? $this->__('post.public_yes')
            : $this->__('post.public_no'));
    }
}
