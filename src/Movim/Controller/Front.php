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
    public function handle()
    {
        $r = new Route($this->user);
        $page = $r->find();

        if ($page === null) {
            $this->redirect($r->getRedirect());
        } else {
            $this->runRequest($page);
        }
    }

    public function runRequest($request)
    {
        if ($request == 'ajax' || $request == 'ajaxd') {
            $content = file_get_contents('php://input');

            $payload = $request === 'ajax'
                ? json_decode($content) // Simple ajax request to a Widget
                : requestAPI('ajax', post: [ // Ajax request that is going to the daemon
                    'sid' => SESSION_ID,
                    'json' => rawurlencode($content)
                ]);

            if ($payload) {
                $rpc = new RPC;
                $rpc->handleJSON($payload->b);
                $rpc->writeJSON();
            }
            return;
        }

        $className = 'App\\Controllers\\' . ucfirst($request) . 'Controller';
        $c = new $className($this->user);
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
    }
}
