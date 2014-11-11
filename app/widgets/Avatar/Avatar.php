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
        $this->registerEvent('myavatarvalid', 'onAvatarPublished');
        $this->registerEvent('myavatarinvalid', 'onAvatarNotPublished');
        $this->registerEvent('myvcard', 'onMyAvatar');
        
        $this->addcss('avatar.css');
        $this->addjs('avatar.js');        
        
        $cd = new \modl\ContactDAO();
        $me = $cd->get($this->user->getLogin());

        $p = new Picture;
        if(!$p->get($this->user->getLogin())) {
            $this->view->assign(
                'getavatar',
                $this->genCallAjax('ajaxGetAvatar')
                );
            $this->view->assign('form', $this->prepareForm(new \modl\Contact()));
        } else {
            $this->view->assign('getavatar', '');
            $this->view->assign('form', $this->prepareForm($me));
        }
    }
    
    function onMyAvatar($c) {
        $html = $this->prepareForm($c);

        RPC::call('movim_fill', 'avatar_form', $html);
        RPC::commit();
    }

    function prepareForm($me) {
        $avatarform = $this->tpl();

        $p = new Picture;
        $p->get($this->user->getLogin());

        $avatarform->assign('photobin', $p->toBase());

        $avatarform->assign('me',       $me);
        $avatarform->assign(
            'submit',
            $this->genCallAjax('ajaxAvatarSubmit', "movim_form_to_json('avatarform')")
            );
        
        return $avatarform->draw('_avatar_form', true);
    }

    function onAvatarPublished()
    {
        RPC::call('movim_button_reset', '#avatarvalidate');
        Notification::appendNotification($this->__('avatar.updated'), 'success');
        RPC::commit();
    }
    
    function onAvatarNotPublished()
    {
        Notification::appendNotification($this->__('avatar.not_updated'), 'error');
        RPC::commit();
    }
    
    function ajaxGetAvatar() {
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
}
