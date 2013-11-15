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

class FrontController extends BaseController
{
    public function handle() {
        $r = new Route();

        // Note that the request is always specified by 'q'.
        if($r->find($this->fetch_get('q'))) {
            $request = $this->fetch_get('q');
            $this->run_req($request);
        } else {
            $this->run_req('notfound');
        }
    }    

    private function load_controller($request) {
        $class_name = ucfirst($request).'Controller';     
        if(file_exists(APP_PATH . 'controllers/'.$class_name.'.php')) {
            $controller_path = APP_PATH . 'controllers/'.$class_name.'.php';
        }
        else {
            \system\Logs\Logger::log(t("Requested controller '%s' doesn't exist.", $class_name));
            exit;
        }

        require_once($controller_path);
        return new $class_name();
    }
    
    /*
     * Here we load, instanciate and execute the correct controller
     */
    public function run_req($request) {
        $c = $this->load_controller($request);

        if(is_callable(array($c, 'load'))) {
            $c->name = $request;
            $c->load();
            $c->check_session();
            $c->dispatch();
            
            // If the controller ask to display a different page
            if($request != $c->name) {
                $new_name = $c->name;
                $c = $this->load_controller($new_name);
                $c->name = $new_name;
                $c->load();
                $c->dispatch();
            }

            // We display the page !
            $c->display();
        } else {
            \system\Logs\Logger::log(t("Could not call the load method on the current controller"));
        }
    }
}
