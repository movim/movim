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

class Avatar extends WidgetBase
{
    function WidgetLoad()
    {
        $this->registerEvent('myavatarvalid', 'onAvatarPublished');
        $this->registerEvent('myavatarinvalid', 'onAvatarNotPublished');
        $this->addcss('avatar.css');
        $this->addjs('avatar.js');        
        
        $cd = new \modl\ContactDAO();
        $me = $cd->get($this->user->getLogin());
        $this->view->assign('me',       $me);

        $this->view->assign(
            'submit',
            $this->genCallAjax('ajaxAvatarSubmit', "movim_form_to_json('avatarform')")
            );
    }

    function onAvatarPublished()
    {
        RPC::call('movim_button_reset', '#avatarvalidate');
        Notification::appendNotification(t('Avatar Updated'), 'success');
        RPC::commit();
    }
    
    function onAvatarNotPublished()
    {
        Notification::appendNotification(t('Avatar Not Updated'), 'error');
        RPC::commit();
    }

    function ajaxAvatarSubmit($avatar)
    {
        $cd = new \modl\ContactDAO();
        $c = $cd->get($this->user->getLogin());

        if($c == null)
            $c = new modl\Contact();
            
        $c->phototype       = $avatar->phototype->value;
        $c->photobin        = $avatar->photobin->value;

        $c->createThumbnails();

        $cd->set($c);
        
        $r = new moxl\AvatarSet();
        $r->setData($avatar->photobin->value)->request();
    }
}
