<?php

/**
 * @package Widgets
 *
 * @file Profile.php
 * This file is part of MOVIM.
 *
 * @brief The Profile widget
 *
 * @author Timothée    Jaussoin <edhelas_at_gmail_dot_com>
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

class Profile extends WidgetCommon
{
    private static $status;

    function load()
    {
        $this->addcss('profile.css');
        $this->addjs('profile.js');
        $this->registerEvent('myvcard', 'onMyVcardReceived');
        $this->registerEvent('mypresence', 'onMyPresence');
    }
    
    function onMyVcardReceived($vcard = false)
    {
        $html = $this->prepareVcard($vcard);
        RPC::call('movim_fill', 'profile', $html);
    }

    function prepareVcard($vcard = false)
    {
        $cd = new \Modl\ContactDAO();
        $contact = $cd->get($this->user->getLogin());

        $vcardview = $this->tpl();
        $vcardview->assign('contact',           $contact);

        return $vcardview->draw('_profile_vcard', true);
    }
}
