<?php
namespace Movim\Controller;

use Monolog\Logger;
use Monolog\Handler\SyslogHandler;

class Front extends Base
{
    public function handle() {
        $r = new \Route;
        $this->runRequest($r->find());
    }

    private function loadController($request) {
        $class_name = ucfirst($request).'Controller';
        if(file_exists(APP_PATH . 'controllers/'.$class_name.'.php')) {
            $controller_path = APP_PATH . 'controllers/'.$class_name.'.php';
        }
        else {
            $log = new Logger('movim');
            $log->pushHandler(new SyslogHandler('movim'));
            $log->addError(__("Requested controller '%s' doesn't exist.", $class_name));
            exit;
        }

        require_once($controller_path);
        return new $class_name();
    }

    /*
     * Here we load, instanciate and execute the correct controller
     */
    public function runRequest($request) {
        $c = $this->loadController($request);

        $sess = \Sessionx::start();
        $sess->refreshCookie();

        if(is_callable(array($c, 'load'))) {
            $c->name = $request;
            $c->load();
            $c->checkSession();
            $c->dispatch();

            // If the controller ask to display a different page
            if($request != $c->name) {
                $new_name = $c->name;
                $c = $this->loadController($new_name);
                $c->name = $new_name;
                $c->load();
                $c->dispatch();
            }

            // We display the page !
            $c->display();
        } else {
            $log = new Logger('movim');
            $log->pushHandler(new SyslogHandler('movim'));
            $log->addError(t("Could not call the load method on the current controller"));
        }
    }
}
