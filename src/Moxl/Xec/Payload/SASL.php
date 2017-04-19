<?php

namespace Moxl\Xec\Payload;

use Moxl\Xec\Action\Register\Get;
use Movim\Session;

class SASL extends Payload
{
    public function handle($stanza, $parent = false)
    {
        $mec = (array)$stanza->mechanism;

        /*
         * Weird behaviour on old eJabberd servers, fixed on the new versions
         * see https://github.com/processone/ejabberd/commit/2d748115
         */
        if(isset($parent->starttls) && isset($parent->starttls->required)) {
            return;
        }

        $session = Session::start();
        $user = $session->get('username');

        if($user) {
            if(!is_array($mec)) {
                $mec = [$mec];
            }

            $mecchoice = str_replace('-', '', \Moxl\Auth::mechanismChoice($mec));

            $session->set('mecchoice', $mecchoice);

            \Moxl\Utils::log("/// MECANISM CHOICE ".$mecchoice);

            if(method_exists('\Moxl\Auth','mechanism'.$mecchoice)) {
                call_user_func('Moxl\Auth::mechanism'.$mecchoice);
            } else {
                \Moxl\Utils::log("/// MECANISM CHOICE NOT FOUND");
            }
        } else {
            $g = new Get;
            $g->setTo($session->get('host'))->request();
        }
    }
}
