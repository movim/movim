<?php

namespace App\Widgets\CommunityHeader;

use App\Post;
use Movim\Widget\Base;
use Moxl\Stanza\Space;
use Moxl\Xec\Action\Disco\Request;
use Moxl\Xec\Action\Pubsub\Subscribe;
use Moxl\Xec\Action\Pubsub\Unsubscribe;
use Moxl\Xec\Action\PubsubSubscription\Add as SubscriptionAdd;
use Moxl\Xec\Action\PubsubSubscription\Remove as SubscriptionRemove;
use Moxl\Xec\Action\Pubsub\TestPostPublish;
use Moxl\Xec\Payload\Packet;
use stdClass;

class CommunityHeader extends Base
{
    public function load()
    {
        $this->registerEvent('disco_request_handle', 'onDiscoRequest', 'community');
        $this->registerEvent('pubsub_subscribe_handle', 'onSubscribed');
        $this->registerEvent('pubsub_subscribe_errorunsupported', 'onSubscriptionUnsupported');
        $this->registerEvent('pubsubsubscription_remove_handle', 'onUnsubscribed');
        $this->registerEvent('pubsub_testpostpublish_handle', 'onTestPublish');
        $this->registerEvent('pubsub_testpostpublish_error', 'onTestPublishError');
        $this->registerEvent('pubsub_setconfig_handle', 'onConfigSaved', 'community');

        $this->addjs('communityheader.js');
    }

    public function onDiscoRequest(Packet $packet)
    {
        $info = $packet->content;

        if ($info->type == Space::NAMESPACE) {
            $this->rpc('MovimUtils.redirect', $this->route(
                'space',
                [$info->server, $info->node]
            ));
            return;
        }

        if (
            $info->identities->contains('category', 'pubsub')
            && $info->identities->contains('type', 'leaf')
            && !$info->isMicroblogCommentsNode()
        ) {
            $this->rpc('MovimTpl.fill', '#community_header', $this->prepareHeader($info->server, $info->node));
        }
    }

    public function onConfigSaved(Packet $packet)
    {
        $this->rpc('CommunityHeader.getMetadata');
    }

    public function onTestPublish(Packet $packet)
    {
        $this->rpc('MovimUtils.redirect', $this->route(
            'publish',
            [$packet->content['to'], $packet->content['node']]
        ));
    }

    public function onTestPublishError(Packet $packet)
    {
        $this->toast($this->__('publish.no_publication'));
    }

    public function onSubscribed(Packet $packet)
    {
        list($server, $node) = array_values($packet->content);

        if ($node != Post::MICROBLOG_NODE) {
            $this->ajaxGetMetadata($server, $node);
        }

        $this->toast($this->__('communityheader.followed'));
    }

    public function onSubscriptionUnsupported(Packet $packet)
    {
        $this->toast($this->__('communityheader.subscription_unsupported'));
    }

    public function onUnsubscribed(Packet $packet)
    {
        list($server, $node) = array_values($packet->content);

        if ($node != Post::MICROBLOG_NODE) {
            $this->ajaxGetMetadata($server, $node);
        }

        $this->toast($this->__('communityheader.unfollowed'));
    }

    public function ajaxGetMetadata(string $server, string $node)
    {
        if (!validateServerNode($server, $node)) {
            return;
        }

        $r = $this->xmpp(new Request);
        $r->setTo($server)->setNode($node)
            ->request();
    }

    public function ajaxAskSubscribe(string $server, string $node)
    {
        if (!validateServerNode($server, $node)) {
            return;
        }

        $view = $this->tpl();

        $view->assign('server', $server);
        $view->assign('node', $node);
        $view->assign('info', \App\Info::where('server', $server)
            ->where('node', $node)
            ->first());

        $this->dialog($view->draw('_communityheader_subscribe'));
    }

    public function ajaxSubscribe(stdClass $form, string $server, string $node)
    {
        if (!validateServerNode($server, $node)) {
            return;
        }

        $g = $this->xmpp(new Subscribe);
        $g->setTo($server)
            ->setNode($node)
            ->setFrom($this->me->id)
            ->setData(formToArray($form))
            ->request();

        if ($form->share->value) {
            $a = $this->xmpp(new SubscriptionAdd);
            $a->setServer($server)
                ->setNode($node)
                ->setFrom($this->me->id)
                ->request();
        }
    }

    public function ajaxAskUnsubscribe(string $server, string $node)
    {
        if (!validateServerNode($server, $node)) {
            return;
        }

        $view = $this->tpl();

        $view->assign('server', $server);
        $view->assign('node', $node);
        $view->assign('info', \App\Info::where('server', $server)
            ->where('node', $node)
            ->first());

        $this->dialog($view->draw('_communityheader_unsubscribe'));
    }

    public function ajaxUnsubscribe(string $server, string $node)
    {
        if (!validateServerNode($server, $node)) {
            return;
        }

        $subscriptions = $this->me->subscriptions()
            ->where('server', $server)
            ->where('node', $node)
            ->get();

        foreach ($subscriptions as $s) {
            $g = $this->xmpp(new Unsubscribe);
            $g->setTo($server)
                ->setNode($node)
                ->setSubid($s->subid)
                ->setFrom($this->me->id)
                ->request();
        }

        $r = $this->xmpp(new SubscriptionRemove);
        $r->setServer($server)
            ->setNode($node)
            ->setFrom($this->me->id)
            ->request();
    }

    /*
     * Sic, doing this hack and wait to have a proper way to test it in the standard
     */
    public function ajaxTestPublish(string $server, string $node)
    {
        if (!validateServerNode($server, $node)) {
            return;
        }

        $t = $this->xmpp(new TestPostPublish);
        $t->setTo($server)
            ->setNode($node)
            ->request();
    }

    public function prepareHeader(string $server, string $node)
    {
        $view = $this->tpl();

        $info = \App\Info::where('server', $server)
            ->where('node', $node)
            ->first();

        $view->assign('info', $info);
        $view->assign('subscription', $this->me->subscriptions()
            ->where('server', $server)
            ->where('node', $node)
            ->first());
        $view->assign('num', $info ?
            ($info->items > 0)
            ? $info->items
            : \App\Post::where('server', $server)
            ->where('node', $node)
            ->count()
            : 0);
        $view->assign('node', $node);
        $view->assign('server', $server);

        return $view->draw('_communityheader');
    }

    public function display()
    {
        $this->view->assign('server', $this->get('s'));
        $this->view->assign('node', $this->get('n'));
    }
}
