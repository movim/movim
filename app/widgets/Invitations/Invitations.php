<?php

use Moxl\Xec\Action\Presence\Subscribed;
use Moxl\Xec\Action\Presence\Unsubscribed;
use Moxl\Xec\Action\Roster\AddItem;
use Moxl\Xec\Action\Roster\UpdateItem;
use Moxl\Xec\Action\Presence\Subscribe;

use Movim\Session;

class Invitations extends \Movim\Widget\Base
{
    function load()
    {
        $this->addcss('invitations.css');
        $this->addjs('invitations.js');

        $this->registerEvent('subscribe', 'onInvitations');
        $this->registerEvent('roster_additem_handle', 'onInvitations');
        $this->registerEvent('roster_updateitem_handle', 'onInvitations');
        $this->registerEvent('presence_subscribe_handle', 'onInvitations');
        $this->registerEvent('presence_subscribed_handle', 'onInvitations');
    }

    function onInvitations($from = false)
    {
        $this->rpc('MovimTpl.fill', '#invitations_widget', $this->prepareInvitations());

        if (is_string($from)) {
            $contact = App\Contact::find($from);
            if (!$contact) $contact = new App\Contact(['id' => $from]);

            Notification::append(
                'invite|'.$from, $contact->truename,
                $this->__('invitations.wants_to_talk', $contact->truename),
                $contact->getPhoto('s'),
                4);
        }
    }

    function ajaxGet()
    {
        $this->onInvitations();
    }

    /*
     * Create the list of notifications
     * @return string
     */
    function prepareInvitations()
    {
        $invitations = [];

        $session = Session::start();
        $notifs = $session->get('activenotifs');
        if (is_array($notifs)) {
            foreach($notifs as $key => $value) {
                array_push($invitations, \App\Contact::firstOrNew(['id' =>$key]));
            }
        }

        $nft = $this->tpl();
        $nft->assign('invitations', $invitations);
        return $nft->draw('_invitations_from', true);
    }

    function ajaxAccept($jid)
    {
        $jid = echapJid($jid);

        if (!App\User::me()->session->contacts->find($jid)) {
            $r = new AddItem;
            $r->setTo($jid)
              ->request();
        }

        $p = new Subscribe;
        $p->setTo($jid)
          ->request();

        $p = new Subscribed;
        $p->setTo($jid)
          ->request();

        // TODO : move in Moxl
        $session = Session::start();
        $notifs = $session->get('activenotifs');

        unset($notifs[$jid]);

        $session->set('activenotifs', $notifs);
        $n = new Notification;
        $n->ajaxClear('invite|'.$jid);

        $this->onInvitations();
    }

    function ajaxRefuse($jid)
    {
        $jid = echapJid($jid);

        $p = new Unsubscribed;
        $p->setTo($jid)
          ->request();

        // TODO : move in Moxl
        $session = Session::start();
        $notifs = $session->get('activenotifs');

        unset($notifs[$jid]);

        $session->set('activenotifs', $notifs);

        $this->onInvitations();
        $n = new Notification;
        $n->ajaxClear('invite|'.$jid);
    }
}
