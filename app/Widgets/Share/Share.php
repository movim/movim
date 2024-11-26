<?php

namespace App\Widgets\Share;

use Respect\Validation\Validator;

use Movim\Widget\Base;
use Movim\Session;
use Movim\XMPPUri;

class Share extends Base
{
    public function load()
    {
        $this->addjs('share.js');
    }

    public function ajaxGet($link)
    {
        $validateUrl = Validator::url();

        if ($validateUrl->validate($link)
        && substr($link, 0, 4) == 'http') {
            // Pre-resolve the link
            (new \App\Url)->resolve($link);

            $session = Session::instance();
            $session->set('share_url', $link);

            $this->rpc('Share.redirect', $this->route('publish'));
        } else {
            $uri = new XMPPUri($link);
            $route = $uri->getRoute();

            if ($route) {
                $this->rpc('MovimUtils.redirect', $route);
            }
        }
    }
}
