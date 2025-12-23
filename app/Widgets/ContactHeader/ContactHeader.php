<?php

namespace App\Widgets\ContactHeader;

use App\Post;
use App\Widgets\Chats\Chats;
use App\Widgets\Dialog\Dialog;
use Movim\Widget\Base;
use Moxl\Xec\Action\Disco\Request;
use Moxl\Xec\Action\Pubsub\Subscribe;
use Moxl\Xec\Action\Pubsub\Unsubscribe;
use Moxl\Xec\Action\Roster\UpdateItem;
use Moxl\Xec\Payload\Packet;

class ContactHeader extends Base
{
    public function load()
    {
        $this->registerEvent('roster_additem_handle', 'onUpdate');
        $this->registerEvent('roster_updateitem_handle', 'onUpdate', 'contact');
        $this->registerEvent('roster_removeitem_handle', 'onUpdate', 'contact');
        $this->registerEvent('vcard_get_handle', 'onUpdate', 'contact');
        $this->registerEvent('vcard4_get_handle', 'onUpdate', 'contact');
        $this->registerEvent('pubsubsubscription_add_handle', 'onSubscription', 'contact');
        $this->registerEvent('pubsubsubscription_remove_handle', 'onSubscription', 'contact');
        $this->registerEvent('pubsub_subscribe_errorpresencesubscriptionrequired', 'onSubscriptionPresenceRequired', 'contact');
        $this->registerEvent('pubsub_subscribe_errorunsupported', 'onSubscriptionUnsupported', 'contact');

        $this->addjs('contactheader.js');
    }

    public function onUpdate(Packet $packet)
    {
        $this->refreshHeader($packet->content);
    }

    public function onSubscription(Packet $packet)
    {
        list($jid, $node) = array_values($packet->content);

        if ($node == Post::MICROBLOG_NODE) {
            $this->refreshHeader($jid);
        }
    }

    public function onSubscriptionPresenceRequired(Packet $packet)
    {
        list($jid, $node) = array_values($packet->content);

        if ($node == Post::MICROBLOG_NODE) {
            $this->toast($this->__('communityposts.subscribe_presencerequired'));
            $this->refreshHeader($jid, disableFollow: true);
        }
    }

    public function onSubscriptionUnsupported(Packet $packet)
    {
        list($jid, $node) = array_values($packet->content);

        if ($node == Post::MICROBLOG_NODE) {
            $this->toast($this->__('communityposts.subscribe_unsupported'));
            $this->refreshHeader($jid, disableFollow: true);
        }
    }

    public function ajaxEditContact($jid)
    {
        if (!validateJid($jid)) {
            return;
        }

        $view = $this->tpl();

        $view->assign('jid', $jid);
        $view->assign('contact', $this->me->session->contacts()->where('jid', $jid)->first());
        $view->assign('groups', $this->me->session->contacts()->select('group')->groupBy('group')->pluck('group')->toArray());

        Dialog::fill($view->draw('_contactheader_edit'));
    }

    public function ajaxEditSubmit($form)
    {
        $rd = $this->xmpp(new UpdateItem);
        $rd->setTo($form->jid->value)
            ->setName($form->alias->value)
            ->setGroup($form->group->value)
            ->request();
    }

    public function ajaxChat(string $jid)
    {
        if (!validateJid($jid)) {
            return;
        }

        $c = new Chats($this->me);
        $c->ajaxOpen($jid, andShow: true);

        $this->rpc('MovimUtils.redirect', $this->route('chat', $jid));
    }

    public function ajaxSubscribe(string $jid)
    {
        if (!validateJid($jid)) return;

        $g = $this->xmpp(new Subscribe);
        $g->setTo($jid)
            ->setNode(Post::MICROBLOG_NODE)
            ->setFrom($this->me->id)
            ->request();
    }

    public function ajaxUnsubscribe(string $jid)
    {
        if (!validateJid($jid)) return;

        $g = $this->xmpp(new Unsubscribe);
        $g->setTo($jid)
            ->setNode(Post::MICROBLOG_NODE)
            ->setFrom($this->me->id)
            ->request();
    }

    public function ajaxGetMetadata(string $jid)
    {
        $r = $this->xmpp(new Request);
        $r->setTo($jid)->setNode(Post::MICROBLOG_NODE)
            ->request();
    }

    public function refreshHeader(string $jid, ?bool $disableFollow = false)
    {
        $this->rpc(
            'MovimTpl.fill',
            '#' . cleanupId($jid) . '_contact_header',
            $this->prepareHeader($jid, $disableFollow)
        );
    }

    public function prepareHeader(string $jid, ?bool $disableFollow = false)
    {
        if (!validateJid($jid)) return;

        $view = $this->tpl();
        $view->assign('subscription', $this->me->subscriptions()
            ->where('server', $jid)
            ->where('node', Post::MICROBLOG_NODE)
            ->first());
        $view->assign('disablefollow', $disableFollow);
        $view->assign('roster', ($this->me->session->contacts()->where('jid', $jid)->first()));
        $view->assign('contact', \App\Contact::firstOrNew(['id' => $jid]));

        return $view->draw('_contactheader');
    }

    public function display()
    {
        $this->view->assign('jid', $this->get('s'));
    }
}
