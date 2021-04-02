<?php

use Respect\Validation\Validator;

use Movim\Widget\Base;
use Movim\Session;

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

            $session = Session::start();
            $session->set('share_url', $link);

            $this->rpc('Share.redirect', $this->route('publish'));
        } else {
            $uri = \explodeXMPPURI($link);

            switch ($uri['type']) {
                case 'room':
                    $this->rpc(
                        'MovimUtils.redirect',
                        $this->route(
                            'chat',
                            [$uri['params'], 'room']
                        )
                    );
                    break;

                case 'post':
                case 'community':
                case 'contact':
                    $this->rpc(
                        'MovimUtils.redirect',
                        $this->route(
                            $uri['type'],
                            $uri['params']
                        )
                    );
                    break;
            }
        }
    }
}
