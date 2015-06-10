<?php
/**
 * @package Widgets
 *
 * @file System.php
 * This file is part of MOVIM.
 *
 * @brief Some global configuration.
 *
 * @author Timothée Jaussoin <edhelas@gmail.com>
 */

class System extends WidgetBase {

    function load()
    {

    }

    function display()
    {
        $this->view->assign('base_uri',     BASE_URI);
        $this->view->assign('base_host',    BASE_HOST);
        $this->view->assign('error_uri',    Route::urlize('disconnect'));

        $r = new Route;
        $this->view->assign('current_page', $r->find());
        
        if(!isset($_SERVER['HTTP_MOD_REWRITE']) || !$_SERVER['HTTP_MOD_REWRITE'])
            $this->view->assign('page_key_uri', '?q=');
        else
            $this->view->assign('page_key_uri', '');

        if(FAIL_SAFE != null)
            $this->view->assign('fail_safe',    FAIL_SAFE);
        else
            $this->view->assign('fail_safe',    '');

        $this->view->assign('secure_websocket',    file_get_contents(CACHE_PATH.'websocket'));

        // And we load some public values of the system configuration
        $cd = new \Modl\ConfigDAO();
        $config = $cd->get();
        
        $public_conf = array(
            'bosh_url' => $config->boshurl,
            'timezone' => $config->timezone
            );
        $this->view->assign('server_conf', json_encode($public_conf));
    }
}
