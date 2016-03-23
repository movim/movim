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
use forxer\Gravatar\Gravatar;

class Avatar extends \Movim\Widget\Base
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

    function onMyAvatar($packet)
    {
        $me = $packet->content;
        $html = $this->prepareForm($me);

        RPC::call('movim_fill', 'avatar_form', $html);
        Notification::append(null, $this->__('avatar.updated'));
    }

    function onMyAvatarError()
    {
        $cd = new \modl\ContactDAO();
        $me = $cd->get();
        $html = $this->prepareForm($me);

        RPC::call('movim_fill', 'avatar_form', $html);
        Notification::append(null, $this->__('avatar.not_updated'));
    }

    function prepareForm($me)
    {
        $avatarform = $this->tpl();

        $p = new Picture;
        $p->get($this->user->getLogin());

        $avatarform->assign('photobin', $p->toBase());

        $avatarform->assign('me',       $me);

        if(isset($me->email)) {
            $result = requestURL(Gravatar::profile($me->email, 'json'), 3);
            $obj = json_decode($result);
            if($obj != 'User not found') {
                $avatarform->assign('gravatar_bin', base64_encode(requestURL('http://www.gravatar.com/avatar/'.$obj->entry[0]->hash.'?s=250')));
                $avatarform->assign('gravatar', $obj);
            }
        }

        $avatarform->assign(
            'submit',
            $this->call('ajaxSubmit', "movim_form_to_json('avatarform')")
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

    function ajaxGetForm()
    {
        $cd = new \modl\ContactDAO();
        $me = $cd->get();

        RPC::call('MovimTpl.fill', '#avatar_form', $this->prepareForm($me));
    }

    function ajaxSubmit($avatar)
    {
        $r = new Set;
        $r->setData($avatar->photobin->value)->request();
    }

    function display()
    {
        $p = new Picture;
        if(!$p->get($this->user->getLogin())) {
            $this->view->assign(
                'getavatar',
                $this->call('ajaxGetAvatar')
                );
            $this->view->assign('form', $this->prepareForm(new \modl\Contact()));
        } else {
            $this->view->assign('getavatar', '');
        }
    }
}
