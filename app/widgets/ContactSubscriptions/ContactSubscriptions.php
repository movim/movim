<?php

use Movim\Widget\Base;

use Moxl\Xec\Action\PubsubSubscription\Get as GetPubsubSubscriptions;

class ContactSubscriptions extends Base
{
    public function load()
    {
        $this->addjs('contactsubscriptions.js');
        $this->registerEvent('pubsubsubscription_get_handle', 'onPubsubSubscriptionReceived', 'contact');
    }

    public function onPubsubSubscriptionReceived($packet)
    {
        $jid = $packet->content;
        $this->rpc('MovimTpl.fill', '#'.cleanupId($jid) . '_contact_subscriptions', $this->prepareSubscriptions($jid));
        $this->rpc('Notification_ajaxGet');
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

        $ps = new GetPubsubSubscriptions;
        $ps->setTo(echapJid($jid))->request();
    }

    public function display()
    {
        $this->view->assign('jid', $this->get('s'));
    }
}
