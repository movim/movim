<?php

namespace Moxl\Xec\Payload;

use Moxl\Xec\Action\Register\Get;
use Movim\Session;
use Moxl\Authentication;

class SASL extends Payload
{
    public function handle($stanza, $parent = false)
    {
        $mechanisms = (array)$stanza->mechanism;

        /*
         * Weird behaviour on old eJabberd servers, fixed on the new versions
         * see https://github.com/processone/ejabberd/commit/2d748115
         */
        if (isset($parent->starttls)
        && isset($parent->starttls->required)) {
            return;
        }

        $session = Session::start();

        if ($session->get('password')) {
            if (!is_array($mechanisms)) {
                $mechanisms = [$mechanisms];
            }

            $auth = Authentication::getInstance();
            $auth->choose($mechanisms);
            $auth->response();
        } else {
            $g = new Get;
            $g->setTo($session->get('host'))->request();
        }
    }
}
