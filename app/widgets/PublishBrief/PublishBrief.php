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

            $p->request();
        } else {
            $this->rpc('PublishBrief.enableSend');
        }
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
    }
}
