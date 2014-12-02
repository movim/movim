<?php

/**
 * @package Widgets
 *
 * @file Avatar.php
 * This file is part of Movim.
 * 
 * @brief A widget which display all the infos of a contact, vcard 4 version
 *
 * @author Timothée    Jaussoin <edhelas_at_gmail_dot_com>

 * Copyright (C)2013 MOVIM project
 * 
 * See COPYING for licensing information.
 */

use Moxl\Xec\Action\Avatar\Get;
use Moxl\Xec\Action\Avatar\Set;

class Avatar extends WidgetBase
{
    function load()
    {
        $this->addcss('avatar.css');
        $this->addjs('avatar.js');
        
        $this->registerEvent('avatar_get_handle', 'onMyAvatar');
        $this->registerEvent('avatar_set_handle', 'onMyAvatar');
        $this->registerEvent('avatar_set_errorfeaturenotimplemented', 'onMyAvatarError');
        $this->registerEvent('avatar_set_errorbadrequest', 'onMyAvatarError');
        $this->registerEvent('avatar_set_errornotallowed', 'onMyAvatarError');
    }
    
    function onMyAvatar($me)
    {
        $me = $packet->content;
        $html = $this->prepareForm($me);

        RPC::call('movim_fill', 'avatar_form', $html);
        Notification::appendNotification($this->__('avatar.updated'), 'success');
    }

    function onMyAvatarError()
    {
        $cd = new \modl\ContactDAO();
        $me = $cd->get();
        $html = $this->prepareForm($me);

        RPC::call('movim_fill', 'avatar_form', $html);
        Notification::appendNotification($this->__('avatar.not_updated'), 'error');
    }

    function prepareForm($me)
    {
        $avatarform = $this->tpl();

        $p = new Picture;
        $p->get($this->user->getLogin());

        $avatarform->assign('photobin', $p->toBase());

        $avatarform->assign('me',       $me);
        $avatarform->assign(
            'submit',
            $this->call('ajaxAvatarSubmit', "movim_form_to_json('avatarform')")
            );
        
        return $avatarform->draw('_avatar_form', true);
    }

    function ajaxGetAvatar()
    {
        $r = new Get;
        $r->setTo($this->user->getLogin())
          ->setMe()
          ->request();
    }

    function ajaxAvatarSubmit($avatar)
    {
        $p = new \Picture;
        $p->fromBase((string)$avatar->photobin->value);
        $p->set($this->user->getLogin());
        
        $r = new Set;
        $r->setData($avatar->photobin->value)->request();
    }

    function display()
    {
        $cd = new \modl\ContactDAO();
        $me = $cd->get();

        $p = new Picture;
        if(!$p->get($this->user->getLogin())) {
            $this->view->assign(
                'getavatar',
                $this->call('ajaxGetAvatar')
                );
            $this->view->assign('form', $this->prepareForm(new \modl\Contact()));
        } else {
            $this->view->assign('getavatar', '');
            $this->view->assign('form', $this->prepareForm($me));
        }
    }
}
