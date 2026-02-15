<?php

namespace App\Widgets\ContactSubscriptions;

use App\Subscription;
use Movim\Widget\Base;

use Moxl\Xec\Action\PubsubSubscription\Get as GetPubsubSubscriptions;
use Moxl\Xec\Payload\Packet;

class ContactSubscriptions extends Base
{
    public function load()
    {
        $this->addjs('contactsubscriptions.js');
        $this->registerEvent('pubsubsubscription_get_handle', 'onPubsubSubscriptionReceived', 'contact');
    }

    public function onPubsubSubscriptionReceived(Packet $packet)
    {
        if ($packet->content['node'] == Subscription::PUBLIC_NODE) {
            $jid = $packet->content['to'];
            $this->rpc('MovimTpl.fill', '#' . cleanupId($jid) . '_contact_subscriptions', $this->prepareSubscriptions($jid));
            $this->rpc('Notif_ajaxGet');
        }
    }

    public function prepareSubscriptions($jid)
    {
        $view = $this->tpl();
        $view->assign('subscriptions', \App\Subscription::where('jid', $jid)
            ->where('public', true)
            ->get());

        return $view->draw('_contactsubscriptions');
    }

    public function ajaxRefresh($jid)
    {
        if (!validateJid($jid)) {
            return;
        }

        $ps = $this->xmpp(new GetPubsubSubscriptions);
        $ps->setTo($jid)->request();
    }

    public function display()
    {
        $this->view->assign('jid', $this->get('s'));
    }
}
