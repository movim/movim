<?php

/**
 * @package Widgets
 *
 * @file Statistics.php
 * This file is part of MOVIM.
 *
 * @brief The administration widget.
 *
 * @author Timothée Jaussoin <edhelas@gmail.com>
 * *
 * Copyright (C)2014 MOVIM project
 *
 * See COPYING for licensing information.
 */
 
class Api extends WidgetBase {
    function load()
    {
    }

    function display()
    {
        $this->view->assign(
            'infos',
            $this->__(
                'api.info',
                '<a href="http://api.movim.eu/" target="_blank">',
                '</a>',
                '<a href="'.$this->route('pods').'">',
                '</a>'));
        
        $json = requestURL(MOVIM_API.'status', 1, array('uri' => BASE_URI));
        $json = json_decode($json);

        $cd = new \Modl\ConfigDAO();
        $config = $cd->get();

        if(isset($json)) {
            $this->view->assign('json', $json);
            if($json->status == 200) {
                $this->view->assign('unregister', $this->call('ajaxUnregister'));
                $this->view->assign('unregister_status', $config->unregister);
            } else {
                $config->unregister = false;
                $cd->set($config);
                $this->view->assign('register', $this->call('ajaxRegister'));
            }
        } else {
            $this->view->assign('json', null);
        }
    }

    function ajaxRegister()
    {
        $rewrite = false;
        if(isset($_SERVER['HTTP_MOD_REWRITE']) && $_SERVER['HTTP_MOD_REWRITE']) {
            $rewrite = true;
        } 
        
        $json = requestURL(
            MOVIM_API.'register',
            1,
            array(
                'uri' => BASE_URI,
                'rewrite' => $rewrite));

        $json = json_decode($json);

        if(isset($json) && $json->status == 200) {
            RPC::call('movim_reload_this');
            Notification::append(null, $this->__('api.conf_updated'));
        }
    }

    function ajaxUnregister()
    {
        $cd = new \Modl\ConfigDAO();
        $config = $cd->get();
        
        $config->unregister = !$config->unregister;
        $cd->set($config);

        RPC::call('movim_reload_this');
        RPC::commit();
    }
}
