<?php

use App\Configuration;
use Movim\Widget\Base;
use App\User;

class Infos extends Base
{
    function display()
    {
        $configuration = Configuration::get();
        $connected = (int)requestAPI('started');

        $infos = [
            'url'           => BASE_URI,
            'language'      => $configuration->locale,
            'whitelist'     => $configuration->xmppwhitelist,
            'description'   => $configuration->description,
            'unregister'    => $configuration->unregister,
            'php_version'   => phpversion(),
            'version'       => APP_VERSION,
            'population'    => User::count(),
            'linked'        => (int)requestAPI('linked'),
            'started'       => $connected,
            'connected'     => $connected
        ];

        $this->view->assign('json', json_encode($infos));
    }
}
