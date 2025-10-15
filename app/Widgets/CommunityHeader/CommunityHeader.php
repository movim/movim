<?php

namespace App\Widgets\CommunityHeader;

use App\Widgets\Dialog\Dialog;
use Movim\Widget\Base;

use Moxl\Xec\Action\Disco\Request;
use Moxl\Xec\Action\Pubsub\Subscribe;
use Moxl\Xec\Action\Pubsub\Unsubscribe;
use Moxl\Xec\Action\PubsubSubscription\Add as SubscriptionAdd;
use Moxl\Xec\Action\PubsubSubscription\Remove as SubscriptionRemove;
use Moxl\Xec\Action\Pubsub\TestPostPublish;
use Moxl\Xec\Payload\Packet;

class CommunityHeader extends Base
{
    public function load()
    {
        $this->registerEvent('disco_request_handle', 'onDiscoRequest', 'community');
        $this->registerEvent('pubsub_subscribe_handle', 'onSubscribed');
        $this->registerEvent('pubsub_subscribe_errorunsupported', 'onSubscriptionUnsupported');
        $this->registerEvent('pubsubsubscription_remove_handle', 'onUnsubscribed');
        $this->registerEvent('pubsub_testpostpublish_handle', 'tonTestPublish');
        $this->registerEvent('pubsub_testpostpublish_error', 'tonTestPublishError');
        $this->registerEvent('pubsub_setconfig_handle', 'onConfigSaved', 'community');

        $this->addjs('communityheader.js');
        $this->addcss('communityheader.css');
    }

    public function onDiscoRequest(Packet $packet)
    {
        $info = $packet->content;

        if (!$info->isMicroblogCommentsNode()) {
            $this->rpc('MovimTpl.fill', '#community_header', $this->prepareHeader($info->server, $info->node));
        }
    }

    public function onConfigSaved(Packet $packet)
    {
        $this->rpc('CommunityHeader.getMetadata');
    }

    public function tonTestPublish(Packet $packet)
    {
        $this->rpc('MovimUtils.redirect', $this->route(
            'publish',
            [$packet->content->to, $packet->content->node]
        ));
    }

    public function tonTestPublishError(Packet $packet)
    {
        $this->toast($this->__('publish.no_publication'));
    }

    public function onSubscribed(Packet $packet)
    {
        list($origin, $node) = array_values($packet->content);

        $this->ajaxGetMetadata($origin, $node);

        $this->toast($this->__('communityheader.subscribed'));
    }

    public function onSubscriptionUnsupported(Packet $packet)
    {
        $this->toast($this->__('communityheader.subscription_unsupported'));
    }

    public function onUnsubscribed(Packet $packet)
    {
        list($origin, $node) = array_values($packet->content);

        $this->ajaxGetMetadata($origin, $node);

        $this->toast($this->__('communityheader.unsubscribed'));
    }

    public function ajaxGetMetadata($origin, $node)
    {
        if (!validateServerNode($origin, $node)) {
            return;
        }

        $r = new Request;
        $r->setTo($origin)->setNode($node)
            ->request();
    }

    public function ajaxAskSubscribe($origin, $node)
    {
        if (!validateServerNode($origin, $node)) {
            return;
        }

        $view = $this->tpl();

        $view->assign('server', $origin);
        $view->assign('node', $node);
        $view->assign('info', \App\Info::where('server', $origin)
            ->where('node', $node)
            ->first());

        Dialog::fill($view->draw('_communityheader_subscribe'));
    }

    public function ajaxSubscribe($form, $origin, $node)
    {
        if (!validateServerNode($origin, $node)) {
            return;
        }

        $g = new Subscribe;
        $g->setTo($origin)
            ->setNode($node)
            ->setFrom($this->me->id)
            ->setData(formToArray($form))
            ->request();

        if ($form->share->value) {
            $a = new SubscriptionAdd;
            $a->setServer($origin)
                ->setNode($node)
                ->setFrom($this->me->id)
                ->request();
        }
    }

    public function ajaxAskUnsubscribe($origin, $node)
    {
        if (!validateServerNode($origin, $node)) {
            return;
        }

        $view = $this->tpl();

        $view->assign('server', $origin);
        $view->assign('node', $node);
        $view->assign('info', \App\Info::where('server', $origin)
            ->where('node', $node)
            ->first());

        Dialog::fill($view->draw('_communityheader_unsubscribe'));
    }

    public function ajaxUnsubscribe($origin, $node)
    {
        if (!validateServerNode($origin, $node)) {
            return;
        }

        $subscriptions = $this->me->subscriptions()
            ->where('server', $origin)
            ->where('node', $node)
            ->get();

        foreach ($subscriptions as $s) {
            $g = new Unsubscribe;
            $g->setTo($origin)
                ->setNode($node)
                ->setSubid($s->subid)
                ->setFrom($this->me->id)
                ->request();
        }

        $r = new SubscriptionRemove;
        $r->setServer($origin)
            ->setNode($node)
            ->setFrom($this->me->id)
            ->request();
    }

    /*
     * Sic, doing this hack and wait to have a proper way to test it in the standard
     */
    public function ajaxTestPublish($origin, $node)
    {
        if (!validateServerNode($origin, $node)) {
            return;
        }

        $t = new TestPostPublish;
        $t->setTo($origin)
            ->setNode($node)
            ->request();
    }

    public function prepareHeader($origin, $node)
    {
        $view = $this->tpl();

        $info = \App\Info::where('server', $origin)
            ->where('node', $node)
            ->first();

        $view->assign('info', $info);
        $view->assign('subscription', $this->me->subscriptions()
            ->where('server', $origin)
            ->where('node', $node)
            ->first());
        $view->assign('num', $info ?
            ($info->items > 0)
            ? $info->items
            : \App\Post::where('server', $origin)
            ->where('node', $node)
            ->count()
            : 0);
        $view->assign('node', $node);
        $view->assign('server', $origin);

        return $view->draw('_communityheader');
    }

    public function display()
    {
        $this->view->assign('server', $this->get('s'));
        $this->view->assign('node', $this->get('n'));
    }
}
