<?php
/**
 * @file FrontController.php
 * This file is part of MOVIM.
 *
 * @brief Handles incoming static pages requests.
 *
 * @author edhelas <edhelas@gmail.com>
 *
 * Copyright (C)2013 MOVIM Project
 *
 * See COPYING for licensing deatils.
 */

class FrontController
{
    public function handle() {
        $r = new Route();
        // Note that the request is always specified by 'q'.
        if($request = $this->fetch_get('q')) {
            $this->run_req($request);
        } else {
            $this->error404();
        }
    }
    
    /*
     * Here we load, instanciate and execute the correct controller
     */
    public function run_req($request) {
        if(file_exists(APP_PATH . 'controllers/'.ucfirst($request).'.php')) {
            $controller_path = file_exists(APP_PATH . 'controllers/'.ucfirst($request).'.php');
        }
        else {
            Logger::log(t("Requested controller '%s' doesn't exist.", $request));
        }

        require_once($controller_path);
        $c = new $request();
        
        if(is_callable(array($c, 'load'))) {
            $c->load();
        } else {
            Logger::log(t("Could not call the load method on the current controller"));
        }
    }
}
