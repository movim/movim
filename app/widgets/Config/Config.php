<?php

/**
 * @package Widgets
 *
 * @file Wall.php
 * This file is part of MOVIM.
 *
 * @brief The configuration form
 *
 * @author Timothée Jaussoin <edhelas_at_gmail_dot_com>
 *
 * @version 1.0
 * @date 28 October 2010
 *
 * Copyright (C)2010 MOVIM project
 *
 * See COPYING for licensing information.
 */

use Moxl\Xec\Action\Storage\Get;
use Moxl\Xec\Action\Storage\Set;

class Config extends WidgetBase
{
    function load()
    {
        $this->addjs('color/jscolor.js');
        $this->registerEvent('config', 'onConfig');

        /* We load the user configuration */
        $this->view->assign('languages', loadLangArray());
        $this->view->assign('me',        $this->user->getLogin());
        $this->view->assign('conf',      $this->user->getConfig('language'));
        $this->view->assign('color',     $this->user->getConfig('color'));
        $this->view->assign('size',      $this->user->getConfig('size'));

        if($this->user->getConfig('chatbox'))
            $this->view->assign('chatbox', 'checked');
        else
            $this->view->assign('chatbox', '');
        
        $this->view->assign('submit',    
            $this->genCallAjax(
                'ajaxSubmit', 
                "movim_parse_form('general')"
            )
                . "this.className='button icon color orange loading'; 
                    this.onclick=null;"
        );
    }
    
    function onConfig(array $data)
    {
        $this->user->setConfig($data);
        RPC::call('movim_reload_this');
        Notification::appendNotification($this->__('config.updated'));
    }

    function ajaxSubmit($data) {
        $config = $this->user->getConfig();
        if(isset($config))
            $data = array_merge($config, $data);

        $s = new Set;
        $s->setXmlns('movim:prefs')
          ->setData(serialize($data))
          ->request();
    }

    function ajaxGet() {
        $s = new Get;
        $s->setXmlns('movim:prefs')
          ->request();
    }
}
