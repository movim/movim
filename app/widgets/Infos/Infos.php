<?php

use Movim\Widget\Base;

use App\Configuration;
use App\User;

class Infos extends Base
{
    public function display()
    {
        $configuration = Configuration::get();
        $connected = (int)requestAPI('started');

        $gitHeadPath = DOCUMENT_ROOT . '/.git/refs/heads/master';
        $hash = file_exists($gitHeadPath) ? substr(file_get_contents($gitHeadPath), 0, 7) : 'release';

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
            'connected'     => $connected,
            'commit'        => $hash
        ];

        $this->view->assign('json', json_encode($infos));
    }
}
