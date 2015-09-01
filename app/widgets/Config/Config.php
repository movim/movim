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

use Moxl\Xec\Action\Storage\Set;

class Config extends WidgetBase
{
    function load()
    {
        $this->addjs('color/jscolor.js');
        $this->addjs('config.js');
        $this->registerEvent('storage_set_handle', 'onConfig');
    }

    function prepareConfigForm()
    {
        $view = $this->tpl();

        /* We load the user configuration */
        $this->user->reload();

        $l = Locale::start();

        $view->assign('languages', $l->getList());
        $view->assign('me',        $this->user->getLogin());
        $view->assign('conf',      $this->user->getConfig());

        $view->assign('submit',
            $this->call(
                'ajaxSubmit',
                "movim_parse_form('general')"
            )
                . "this.className='button color orange inactive oppose';
                    this.onclick=null;"
        );

        return $view->draw('_config_form', true);
    }

    function onConfig($package)
    {
        $data = (array)$package->content;
        $this->user->setConfig($data);

        $html = $this->prepareConfigForm();

        RPC::call('movim_fill', 'config_widget', $html);
        RPC::call('Config.load');
        Notification::append(null, $this->__('config.updated'));
    }

    function ajaxSubmit($data)
    {
        $config = $this->user->getConfig();
        if(isset($config))
            $data = array_merge($config, $data);

        $s = new Set;
        $s->setXmlns('movim:prefs')
          ->setData(serialize($data))
          ->request();
    }

    function display()
    {
        $this->view->assign('form', $this->prepareConfigForm());
    }
}
