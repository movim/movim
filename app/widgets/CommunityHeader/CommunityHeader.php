<?php

use Moxl\Xec\Action\Pubsub\GetMetadata;

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
        $this->registerEvent('pubsub_getmetadata_handle', 'onMetadata');
        $this->registerEvent('pubsub_subscribe_handle', 'onSubscribed');
        $this->registerEvent('pubsub_subscribe_errorunsupported', 'onSubscriptionUnsupported');
        $this->registerEvent('pubsub_unsubscribe_handle', 'onUnsubscribed');
        $this->registerEvent('pubsub_testpostpublish_handle', 'onTestPublish');
        $this->registerEvent('pubsub_testpostpublish_error', 'onTestPublishError');

        $this->addjs('communityheader.js');
    }

    function onMetadata($packet)
    {
        list($server, $node) = $packet->content;

        RPC::call('MovimTpl.fill', '#community_header', $this->prepareHeader($server, $node));
    }

    function onTestPublish($packet)
    {
        list($server, $node) = array_values($packet->content);
        $this->rpc('MovimUtils.redirect', $this->route('publish', [$server, $node]));
    }

    function onTestPublishError($packet)
    {
        Notification::append(null, $this->__('publish.no_publication'));
    }

    function onSubscribed($packet)
    {
        list($server, $node) = array_values($packet->content);

        $r = new Rooms;
        $r->setBookmark();

        $this->ajaxGetMetadata($server, $node);

        Notification::append(null, $this->__('communityheader.subscribed'));
    }

    function onSubscriptionUnsupported($packet)
    {
        Notification::append(null, $this->__('communityheader.subscription_unsupported'));
    }

    function onUnsubscribed($packet)
    {
        list($server, $node) = array_values($packet->content);

        $r = new Rooms;
        $r->setBookmark();

        $this->ajaxGetMetadata($server, $node);

        Notification::append(null, $this->__('communityheader.unsubscribed'));
    }

    function ajaxGetMetadata($server, $node)
    {
        if(!$this->validateServerNode($server, $node)) return;

        $r = new GetMetadata;
        $r->setTo($server)->setNode($node)
          ->request();
    }

    function ajaxAskSubscribe($server, $node)
    {
        if(!$this->validateServerNode($server, $node)) return;

        $view = $this->tpl();

        $view->assign('server', $server);
        $view->assign('node', $node);

        $id = new \Modl\ItemDAO;
        $item = $id->getItem($server, $node);

        if(isset($item)) {
            $view->assign('item', $item);
        } else {
            $view->assign('item', null);
        }

        Dialog::fill($view->draw('_communityheader_subscribe', true));
    }

    function ajaxSubscribe($form, $server, $node)
    {
        if(!$this->validateServerNode($server, $node)) return;

        $g = new Subscribe;
        $g->setTo($server)
          ->setNode($node)
          ->setFrom($this->user->getLogin())
          ->setData($form)
          ->request();

        if($form->share->value) {
            $a = new SubscriptionAdd;
            $a->setServer($server)
              ->setNode($node)
              ->setFrom($this->user->getLogin())
              ->request();
        }
    }

    function ajaxAskUnsubscribe($server, $node)
    {
        if(!$this->validateServerNode($server, $node)) return;

        $view = $this->tpl();

        $view->assign('server', $server);
        $view->assign('node', $node);

        $id = new \Modl\ItemDAO;
        $item = $id->getItem($server, $node);

        if(isset($item)) {
            $view->assign('item', $item);
        } else {
            $view->assign('item', null);
        }

        Dialog::fill($view->draw('_communityheader_unsubscribe', true));
    }

    function ajaxUnsubscribe($server, $node)
    {
        if(!$this->validateServerNode($server, $node)) return;

        $sd = new \Modl\SubscriptionDAO;

        foreach($sd->get($server, $node) as $s) {
            $g = new Unsubscribe;
            $g->setTo($server)
              ->setNode($node)
              ->setSubid($s->subid)
              ->setFrom($this->user->getLogin())
              ->request();
        }

        $r = new SubscriptionRemove;
        $r->setServer($server)
          ->setNode($node)
          ->setFrom($this->user->getLogin())
          ->request();
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

    public function prepareHeader($server, $node)
    {
        $id = new \Modl\ItemDAO;
        $item = $id->getItem($server, $node);

        if($item && !$item->logo) {
            $item->setPicture();
            $id->set($item);
        }

        $pd = new \Modl\SubscriptionDAO;
        $subscription = $pd->get($server, $node);

        $view = $this->tpl();

        $view->assign('item', $item);
        $view->assign('subscription', $subscription);
        $view->assign('node', $node);
        $view->assign('server', $server);

        return $view->draw('_communityheader', true);
    }

    private function validateServerNode($server, $node)
    {
        $validate_server = Validator::stringType()->noWhitespace()->length(6, 40);
        $validate_node = Validator::stringType()->length(3, 100);

        if(!$validate_server->validate($server)
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
