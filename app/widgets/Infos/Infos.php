<?php

/**
 * @package Widgets
 *
 * @file Pods.php
 * This file is part of Movim.
 *
 * @brief The Infos widget for the API
 *
 * @author Jaussoin Timothée <edhelas@movim.eu>

 * Copyright (C)2014 Movim project
 *
 * See COPYING for licensing information.
 */
 
class Infos extends WidgetBase
{
    function load() {

    }

    function display()
    {
        // We get the informations
        $pop = 0;
        foreach(scandir(USERS_PATH) as $f)
            if(is_dir(USERS_PATH.'/'.$f))
                $pop++;
        $pop = $pop-2;

        // We get the global configuration
        $cd = new \Modl\ConfigDAO();
        $config = $cd->get();

        $sd = new \Modl\SessionxDAO();

        $infos = array(
                'url'           => BASE_URI,
                'language'      => $config->locale,
                'whitelist'     => $config->xmppwhitelist,
                'timezone'      => $config->timezone,
                'description'   => $config->description,
                'unregister'    => $config->unregister,
                'php_version'   => phpversion(),
                'version'       => APP_VERSION,
                'population'    => $pop,
                'connected'     => $sd->getConnected()
            );
        
        $this->view->assign('json', json_encode($infos));
    }
}
