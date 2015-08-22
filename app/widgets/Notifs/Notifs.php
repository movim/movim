<?php

/**
 * @package Widgets
 *
 * @file Notifs.php
 * This file is part of MOVIM.
 *
 * @brief The notification widget
 *
 * @author Timothée Jaussoin <edhelas@gmail.com>
 *
 * @version 1.0
 * @date 16 juin 2011
 *
 * Copyright (C)2010 MOVIM project
 *
 * See COPYING for licensing information.
 */
use Moxl\Xec\Action\Presence\Subscribed;
use Moxl\Xec\Action\Presence\Unsubscribed;
use Moxl\Xec\Action\Roster\AddItem;
use Moxl\Xec\Action\Roster\UpdateItem;
use Moxl\Xec\Action\Presence\Subscribe;

class Notifs extends WidgetBase
{
    function load()
    {
        $this->addcss('notifs.css');
        $this->addjs('notifs.js');

        $this->registerEvent('subscribe', 'onNotifs');
        $this->registerEvent('roster_additem_handle', 'onNotifs');
        $this->registerEvent('roster_updateitem_handle', 'onNotifs');
        $this->registerEvent('presence_subscribe_handle', 'onNotifs');
        $this->registerEvent('presence_subscribed_handle', 'onNotifs');
    }

    function onNotifs($from = false)
    {
        $html = $this->prepareNotifs();
        RPC::call('movim_fill', 'notifs_widget', $html);
        RPC::call('Notifs.refresh');

        if(is_string($from)) {
            $cd = new \Modl\ContactDAO;
            $contact = $cd->get($from);

            $avatar = $contact->getPhoto('s');
            if($avatar == false) $avatar = null;
            Notification::append('invite|'.$from, $contact->getTrueName(), $this->__('notifs.wants_to_talk', $contact->getTrueName()), $avatar, 4);
        }
    }

    function ajaxGet()
    {
        $this->onNotifs();
    }

    /*
     * Create the list of notifications
     * @return string
     */
    function prepareNotifs()
    {
        $cd = new \Modl\ContactDAO();
        $contacts = $cd->getRosterFrom();

        $invitations = array();

        $session = \Session::start();
        $notifs = $session->get('activenotifs');
        if(is_array($notifs)) {
            foreach($notifs as $key => $value) {
                array_push($invitations, $cd->get($key));
            }
        }

        $nft = $this->tpl();

        $nft->assign('invitations', $invitations);
        $nft->assign('contacts', $contacts);
        return $nft->draw('_notifs_from', true);
    }

    function ajaxAsk($jid)
    {
        $view = $this->tpl();
        $view->assign('jid', $jid);
        Dialog::fill($view->draw('_notifs_confirm', true));
    }

    function ajaxAccept($jid)
    {
        $jid = echapJid($jid);

        $rd = new \Modl\RosterLinkDAO();
        $c  = $rd->get($jid);

        if(isset($c) && $c->groupname) {
            $ui = new UpdateItem;
            $ui->setTo($jid)
               ->setFrom($this->user->getLogin())
               ->setName($c->rostername)
               ->setGroup($c->groupname)
               ->request();
        } else {
            $r = new AddItem;
            $r->setTo($jid)
              ->setFrom($this->user->getLogin())
              ->request();
        }

        $p = new Subscribe;
        $p->setTo($jid)
          ->request();

        $p = new Subscribed;
        $p->setTo($jid)
          ->request();

        // TODO : move in Moxl
        $session = \Session::start();
        $notifs = $session->get('activenotifs');

        unset($notifs[$jid]);

        $session->set('activenotifs', $notifs);
        Notification::ajaxClear('invite|'.$jid);
    }

    function ajaxRefuse($jid)
    {
        $jid = echapJid($jid);

        $p = new Unsubscribed;
        $p->setTo($jid)
          ->request();

        // TODO : move in Moxl
        $session = \Session::start();
        $notifs = $session->get('activenotifs');

        unset($notifs[$jid]);

        $session->set('activenotifs', $notifs);

        $this->onNotifs();
        Notification::ajaxClear('invite|'.$jid);
    }
}
