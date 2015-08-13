<?php

/**
 * @package Widgets
 *
 * @file Logout.php
 * This file is part of MOVIM.
 *
 * @brief The little logout widget.
 *
 * @author Guillaume Pasquet <etenil@etenilsrealm.nl>
 *
 * @version 1.0
 * @date 20 October 2010
 *
 * Copyright (C)2010 MOVIM project
 *
 * See COPYING for licensing information.
 */

use Moxl\Xec\Action\Presence\Chat;
use Moxl\Xec\Action\Presence\Away;
use Moxl\Xec\Action\Presence\DND;
use Moxl\Xec\Action\Presence\XA;
use Moxl\Xec\Action\Presence\Unavailable;
use Moxl\Xec\Action\Pubsub\GetItems;
use Moxl\Stanza\Stream;
use Moxl\Xec\Action\Storage\Get;

class Presence extends WidgetBase
{

    function load()
    {
        $this->addcss('presence.css');
        $this->addjs('presence.js');
        $this->registerEvent('mypresence', 'onMyPresence');
    }

    function onMyPresence($packet)
    {
        $html = $this->preparePresence();
        RPC::call('movim_fill', 'presence_widget', $html);
        Notification::append(null, $this->__('status.updated'));
        RPC::call('Presence.refresh');
        RPC::call('movim_remove_class', '#presence_widget', 'unfolded');
    }

    function onPostDisconnect($data)
    {
        RPC::call('movim_reload',
                       BASE_URI."index.php?q=disconnect");
    }

    function ajaxSet($form = false)
    {
        if($form == false) {
            // We update the cache with our status and presence
            $presence = Cache::c('presence');

            $value = $presence['show'];
            $status = $presence['status'];

            if($presence == null || !isset($presence['show']) || $presence['show'] == '')
                $value = 'chat';

            if($presence == null|| !isset($presence['status']) || $presence['status'] == '')
                $status = $this->__('status.online');
        } else {
            $status = $form['status'];
            $value = $form['value'];
        }

        if(in_array($value, array('chat', 'away', 'dnd', 'xa'))) {
            switch($value) {
                case 'chat':
                    $p = new Chat;
                    $p->setStatus($status)->request();
                    break;
                case 'away':
                    $p = new Away;
                    $p->setStatus($status)->request();
                    break;
                case 'dnd':
                    $p = new DND;
                    $p->setStatus($status)->request();
                    break;
                case 'xa':
                    $p = new XA;
                    $p->setStatus($status)->request();
                    break;
            }
        }

        Cache::c(
            'presence',
            array(
                'status' => $status,
                'show' => $value
                )
        );
    }

    function ajaxLogout()
    {
        $pd = new \Modl\PresenceDAO();

        $session = \Sessionx::start();
        $pd->clearPresence($session->username.'@'.$session->host);

        $session = \Sessionx::start();
        $p = new Unavailable;
        $p->setType('terminate')
          ->setResource($session->resource)
          ->setTo($this->user->getLogin())
          ->request();

        Stream::end();
    }

    function ajaxConfigGet() {
        $s = new Get;
        $s->setXmlns('movim:prefs')
          ->request();
    }

    // We get the server capabilities
    function ajaxServerCapsGet()
    {
        $session = \Sessionx::start();
        $c = new \Moxl\Xec\Action\Disco\Request;
        $c->setTo($session->host)
          ->request();
    }

    // We discover the server services
    function ajaxServerDisco()
    {
        $session = \Sessionx::start();
        $c = new \Moxl\Xec\Action\Disco\Items;
        $c->setTo($session->host)
          ->request();
    }

    // We refresh the bookmarks
    function ajaxBookmarksGet()
    {
        $session = \Sessionx::start();
        $b = new \Moxl\Xec\Action\Bookmark\Get;
        $b->setTo($session->user.'@'.$session->host)
          ->request();
    }

    // We refresh the user (local) configuration
    function ajaxUserRefresh()
    {
        $language = $this->user->getConfig('language');
        if(isset($language)) {
            loadLanguage($language);
        }
    }

    // We refresh our personnal feed
    function ajaxFeedRefresh()
    {
        $r = new GetItems;
        $r->setTo($this->user->getLogin())
          ->setNode('urn:xmpp:microblog:0')
          ->request();
    }

    function ajaxOpenDialog()
    {
        Dialog::fill($this->preparePresenceList());
        RPC::call('Presence.refresh');
    }

    function preparePresence()
    {
        $cd = new \Modl\ContactDAO();
        $pd = new \Modl\PresenceDAO();

        $session = \Sessionx::start();
        $presence = $pd->getPresence($this->user->getLogin(), $session->resource);

        $presencetpl = $this->tpl();

        $contact = $cd->get();
        if($contact == null) {
            $contact = new \Modl\Contact;
        }

        if($presence == null) {
            $presence = new \Modl\Presence;
        }

        $presencetpl->assign('me', $contact);
        $presencetpl->assign('presence', $presence);
        $presencetpl->assign('presencetxt', getPresencesTxt());
        $presencetpl->assign('dialog',      $this->call('ajaxOpenDialog'));

        $html = $presencetpl->draw('_presence', true);

        return $html;
    }

    function preparePresenceList()
    {
        $txt = getPresences();
        $txts = getPresencesTxt();

        $session = \Sessionx::start();

        $pd = new \Modl\PresenceDAO();
        $p = $pd->getPresence($this->user->getLogin(), $session->resource);

        $cd = new \Modl\ContactDAO();
        $contact = $cd->get($this->user->getLogin());
        if($contact == null) {
            $contact = new \Modl\Contact;
        }

        $presencetpl = $this->tpl();

        $presencetpl->assign('contact', $contact);
        $presencetpl->assign('p', $p);
        $presencetpl->assign('txt', $txt);
        $presencetpl->assign('txts', $txts);

        $presencetpl->assign('calllogout',  $this->call('ajaxLogout'));
        $html = $presencetpl->draw('_presence_list', true);

        return $html;
    }

    function display()
    {
        $this->view->assign('presence', $this->preparePresence());
    }
}

?>
