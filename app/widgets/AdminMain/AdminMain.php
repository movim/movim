<?php

/**
 * @package Widgets
 *
 * @file AdminMain.php
 * This file is part of Movim.
 *
 * @brief The main configuration on Movim
 *
 * @author Jaussoin Timothée <edhelas@movim.eu>

 * Copyright (C)2014 Movim project
 *
 * See COPYING for licensing information.
 */
 
class AdminMain extends WidgetBase
{
    function load() {
        $this->addjs('admin.js');

        $form = $_POST;
        $cd = new \Modl\ConfigDAO();
        $config = $cd->get();

        if(isset($form)) {
            if($form['pass'] != '' && $form['repass'] != ''
            && $form['pass'] == $form['repass']) {
                unset($form['repass']);
                $form['pass'] = sha1($form['pass']);
            } else {
                $form['pass'] = $config->pass;
            }

            foreach($form as $key => $value) {
                $config->$key = $value;
            }

            $cd->set($config);
        }
    }

    public function testBosh($url)
    {
        return requestURL($url, 2);
    }

    public function date()
    {
        return date('l jS \of F Y h:i:s A');
    }

    function display()
    {
        $cd = new \Modl\ConfigDAO();
        $config = $cd->get();
        
        $this->view->assign('conf', $cd->get());
        $this->view->assign('logs',
            array(
                0 => t('Empty'),
                1 => t('Syslog'),
                2 => t('Syslog and Files'))
        );
        $this->view->assign('envs',
            array(
                'development' => 'Development',
                'production'  => 'Production')
        );
        
        $this->view->assign('timezones', getTimezoneList());
        $this->view->assign('langs', loadLangArray());
    }
}
