<?php

use Moxl\Xec\Action\Disco\Request;

use Moxl\Xec\Action\Pubsub\Subscribe;
use Moxl\Xec\Action\Pubsub\Unsubscribe;

use Moxl\Xec\Action\PubsubSubscription\Add as SubscriptionAdd;
use Moxl\Xec\Action\PubsubSubscription\Remove as SubscriptionRemove;

use Moxl\Xec\Action\Pubsub\TestPostPublish;

use Respect\Validation\Validator;

class CommunityHeader extends \Movim\Widget\Base
{
    public function load()
    {
        $this->registerEvent('disco_request_handle', 'onDiscoRequest', 'community');
        $this->registerEvent('pubsub_subscribe_handle', 'onSubscribed');
        $this->registerEvent('pubsub_subscribe_errorunsupported', 'onSubscriptionUnsupported');
        $this->registerEvent('pubsub_unsubscribe_handle', 'onUnsubscribed');
        $this->registerEvent('pubsub_testpostpublish_handle', 'onTestPublish');
        $this->registerEvent('pubsub_testpostpublish_error', 'onTestPublishError');

        $this->addjs('communityheader.js');
    }

    function onDiscoRequest($packet)
    {
        list($origin, $node) = $packet->content;

        if((substr($node, 0, 30) != 'urn:xmpp:microblog:0:comments/')) {
            $this->rpc('MovimTpl.fill', '#community_header', $this->prepareHeader($origin, $node));
        }
    }

    function onTestPublish($packet)
    {
        list($origin, $node) = array_values($packet->content);
        $this->rpc('MovimUtils.redirect', $this->route('publish', [$origin, $node]));
    }

    function onTestPublishError($packet)
    {
        Notification::append(null, $this->__('publish.no_publication'));
    }

    function onSubscribed($packet)
    {
        list($origin, $node) = array_values($packet->content);

        $r = new Rooms;
        $r->setBookmark();

        $this->ajaxGetMetadata($origin, $node);

        Notification::append(null, $this->__('communityheader.subscribed'));
    }

    function onSubscriptionUnsupported($packet)
    {
        Notification::append(null, $this->__('communityheader.subscription_unsupported'));
    }

    function onUnsubscribed($packet)
    {
        list($origin, $node) = array_values($packet->content);

        $r = new Rooms;
        $r->setBookmark();

        $this->ajaxGetMetadata($origin, $node);

        Notification::append(null, $this->__('communityheader.unsubscribed'));
    }

    function ajaxGetMetadata($origin, $node)
    {
        if(!$this->validateServerNode($origin, $node)) return;

        $r = new Request;
        $r->setTo($origin)->setNode($node)
          ->request();
    }

    function ajaxAskSubscribe($origin, $node)
    {
        if(!$this->validateServerNode($origin, $node)) return;

        $view = $this->tpl();

        $view->assign('server', $origin);
        $view->assign('node', $node);

        $id = new \Modl\InfoDAO;
        $info = $id->get($origin, $node);

        if(isset($info)) {
            $view->assign('info', $info);
        } else {
            $view->assign('info', null);
        }

        Dialog::fill($view->draw('_communityheader_subscribe', true));
    }

    function ajaxSubscribe($form, $origin, $node)
    {
        if(!$this->validateServerNode($origin, $node)) return;

        $g = new Subscribe;
        $g->setTo($origin)
          ->setNode($node)
          ->setFrom($this->user->getLogin())
          ->setData($form)
          ->request();

        if($form->share->value) {
            $a = new SubscriptionAdd;
            $a->setServer($origin)
              ->setNode($node)
              ->setFrom($this->user->getLogin())
              ->request();
        }
    }

    function ajaxAskUnsubscribe($origin, $node)
    {
        if(!$this->validateServerNode($origin, $node)) return;

        $view = $this->tpl();

        $view->assign('server', $origin);
        $view->assign('node', $node);

        $id = new \Modl\InfoDAO;
        $info = $id->get($origin, $node);

        if(isset($info)) {
            $view->assign('info', $info);
        } else {
            $view->assign('info', null);
        }

        Dialog::fill($view->draw('_communityheader_unsubscribe', true));
    }

    function ajaxUnsubscribe($origin, $node)
    {
        if(!$this->validateServerNode($origin, $node)) return;

        $sd = new \Modl\SubscriptionDAO;

        foreach($sd->get($origin, $node) as $s) {
            $g = new Unsubscribe;
            $g->setTo($origin)
              ->setNode($node)
              ->setSubid($s->subid)
              ->setFrom($this->user->getLogin())
              ->request();
        }

        $r = new SubscriptionRemove;
        $r->setServer($origin)
          ->setNode($node)
          ->setFrom($this->user->getLogin())
          ->request();
    }

    /*
     * Sic, doing this hack and wait to have a proper way to test it in the standard
     */
    function ajaxTestPublish($origin, $node)
    {
        if(!$this->validateServerNode($origin, $node)) return;

        $t = new TestPostPublish;
        $t->setTo($origin)
          ->setNode($node)
          ->request();
    }

    public function prepareHeader($origin, $node)
    {
        $id = new \Modl\InfoDAO;
        $info = $id->get($origin, $node);

        /*
        if($item && !$item->logo) {
            $item->setPicture();
            $id->set($item);
        }
        */
        $pd = new \Modl\SubscriptionDAO;
        $subscription = $pd->get($origin, $node);

        $view = $this->tpl();

        $view->assign('info', $info);
        $view->assign('subscription', $subscription);
        $view->assign('node', $node);
        $view->assign('server', $origin);

        return $view->draw('_communityheader', true);
    }

    private function validateServerNode($origin, $node)
    {
        $validate_server = Validator::stringType()->noWhitespace()->length(6, 40);
        $validate_node = Validator::stringType()->length(3, 100);

        if(!$validate_server->validate($origin)
        || !$validate_node->validate($node)
        ) return false;
        else return true;
    }

    public function display()
    {
        $this->view->assign('server', $this->get('s'));
        $this->view->assign('node', $this->get('n'));
    }
}
