<?php

use Moxl\Xec\Action\Pubsub\PostPublish;

use Movim\Session;

use Respect\Validation\Validator;

class PublishBrief extends \Movim\Widget\Base
{
    function load()
    {
        $this->addjs('publishbrief.js');
        $this->addcss('publishbrief.css');
    }

    function ajaxPublish($form)
    {
        $this->rpc('PublishBrief.disableSend');

        if(Validator::stringType()->notEmpty()->validate(trim($form->title->value))) {
            $p = new PostPublish;
            $p->setFrom($this->user->getLogin())
              ->setTo($this->user->getLogin())
              ->setTitle(htmlspecialchars($form->title->value))
              ->setNode('urn:xmpp:microblog:0');

            if($form->open->value === true) {
                $p->isOpen();
            }

            if(Validator::notEmpty()->url()->validate($form->embed->value)) {
                try {
                    $embed = Embed\Embed::create($form->embed->value);

                    if($embed->type == 'photo') {
                        $p->setImage($embed->images[0]['url'], $embed->title, $embed->images[0]['mime']);
                    } else {
                        if(isset($embed->images)) {
                            $p->setImage($embed->images[0]['url'], $embed->title, $embed->images[0]['mime']);
                        }
                        $p->setLink($form->embed->value, $embed->title, 'text/html', $embed->description, $embed->providerIcon);
                    }
                } catch(Exception $e) {
                    error_log($e->getMessage());
                }
            }

            $p->request();
        } else {
            $this->rpc('PublishBrief.enableSend');
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

            $this->rpc('Dialog_ajaxClear');

            //if(in_array($embed->type, ['photo', 'rich'])) {
                $this->rpc('MovimTpl.fill', '#publishbrief p.embed', $this->prepareEmbed($embed));
            //}
        } catch(Exception $e) {
            error_log($e->getMessage());
        }
    }

    function ajaxClearEmbed()
    {
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

    function ajaxLink()
    {
        $view = $this->tpl();
        Dialog::fill($view->draw('_publishbrief_link', true));
    }

    function ajaxDisplayPrivacy($open)
    {
        if($open) {
            Notification::append(false, $this->__('post.public_yes'));
        } else {
            Notification::append(false, $this->__('post.public_no'));
        }
    }

    function display()
    {
        $this->view->assign('embed', $this->prepareEmbedDefault());
    }
}
