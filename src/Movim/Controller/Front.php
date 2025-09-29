<?php
/*
 * SPDX-FileCopyrightText: 2010 Jaussoin Timothée
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

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

    /**
     * Here we load, instanciate and execute the correct controller
     */
    public function runRequest($request)
    {
        if ($request == 'ajax' || $request == 'ajaxd') {
            $payload = json_decode(file_get_contents('php://input'));

            if ($payload && $payload->b && $payload->b->c) {
                $c = $this->loadController($payload->b->c);
                if (is_callable([$c, 'load'])) $c->load();

                $c->checkSession();
                if ($c->name == 'login') {
                    header('HTTP/1.0 403 Forbidden');
                    exit;
                }
            } else {
                header('HTTP/1.0 403 Forbidden');
                exit;
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
            requestAPI('ajax', post: [
                'sid' => SESSION_ID,
                'json' => rawurlencode(file_get_contents('php://input'))
            ]);

            $rpc = new RPC;
            $rpc->writeJSON();
            return;
        }

        $c = $this->loadController($request);

        if (is_callable([$c, 'load'])) {
            $c->name = $request;
            $c->load();

            if ($c->set_cookie) {
                Cookie::set();
            } else {
                Cookie::clearCookieHeader();
            }

            $c->checkSession();

            if ($request != $c->name) {
                $this->redirect('login');
            }

            $c->dispatch();
            $c->display();
        } else {
            logInfo('Could not call the load method on the current controller');
        }
    }

    public function loadController(string $page)
    {
        $className = 'App\\Controllers\\' . ucfirst($page) . 'Controller';
        return new $className();
    }
}
