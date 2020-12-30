<?php
namespace Movim\Controller;

use Movim\Route;
use Movim\Cookie;
use Movim\RPC;

class Front extends Base
{
    public function handle($forcePage = null)
    {
        $r = new Route;
        $page = $r->find($forcePage);

        if ($page === null) {
            $this->redirect($r->getRedirect());
        } else {
            $this->runRequest($page);
        }
    }

    public function loadController($request)
    {
        $className = ucfirst($request).'Controller';
        if (file_exists(APP_PATH . 'controllers/'.$className.'.php')) {
            $controllerPath = APP_PATH . 'controllers/'.$className.'.php';
        } else {
            \Utils::error("Requested controller $className doesn't exist");
            exit;
        }

        require_once $controllerPath;
        return new $className();
    }

    /**
     * Here we load, instanciate and execute the correct controller
     */
    public function runRequest($request)
    {
        if ($request == 'ajax' || $request == 'ajaxd') {
            $parts = explode('/', parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY));

            if (isset($parts[0])) {
                $c = $this->loadController($parts[0]);
                if (is_callable([$c, 'load'])) $c->load();

                $c->checkSession();
                if ($c->name == 'login') {
                    header('HTTP/1.0 403 Forbidden');
                    exit;
                }
            }
        }

        // Simple ajax request to a Widget
        if ($request === 'ajax') {
            $payload = json_decode(file_get_contents('php://input'));

            if ($payload) {
                $rpc = new RPC;
                $rpc->handleJSON($payload->b);
                $rpc->writeJSON();
            }
            return;
        }

        // Ajax request that is going to the daemon
        if ($request === 'ajaxd') {
            requestAPI('ajax', 2, [
                'sid' => SESSION_ID,
                'json' => rawurlencode(file_get_contents('php://input'))
            ]);
            return;
        }

        $c = $this->loadController($request);

        if (is_callable([$c, 'load'])) {

            // Useful for the daemon
            if (php_sapi_name() != 'cli' && $request == 'login') {
                file_put_contents(CACHE_PATH.'baseuri', BASE_URI);
            }

            $c->name = $request;
            $c->load();

            if ($c->set_cookie) {
                Cookie::refresh();
            } else {
                Cookie::clearCookieHeader();
            }

            $c->checkSession();
            $c->dispatch();

            // If the controller ask to display a different page
            if ($request != $c->name) {
                $this->redirect('login');
            }

            // We display the page!
            $c->display();
        } else {
            \Utils::info('Could not call the load method on the current controller');
        }
    }
}
