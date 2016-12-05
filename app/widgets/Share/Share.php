<?php

use Respect\Validation\Validator;

class Share extends \Movim\Widget\Base
{
    function load()
    {
        $this->addjs('share.js');
    }

    function ajaxGet($link)
    {
        $validate_url = Validator::url();

        if($validate_url->validate($link)
        && substr($link, 0, 4) == 'http') {
            $session = \Session::start();
            $session->set('share_url', $link);
            RPC::call('Share.redirect', $this->route('publish'));
        } elseif(substr($link, 0, 5) == 'xmpp:') {
            $link = str_replace(['xmpp://', 'xmpp:'], '', $link);

            if(substr($link, -5, 5) == '?join') {
                RPC::call(
                    'MovimUtils.redirect',
                    $this->route(
                        'chat', [str_replace('?join', '', $link), 'room']
                    )
                );
            } else {
                RPC::call(
                    'MovimUtils.redirect',
                    $this->route(
                        'contact', $link
                    )
                );
            }
        }
    }

    function display()
    {
    }
}
