<?php

class Infos extends \Movim\Widget\Base
{
    function load() {

    }

    function display()
    {
        // We get the informations
        $pop = 0;
        foreach(scandir(USERS_PATH) as $f)
            if(is_dir(USERS_PATH.'/'.$f))
                $pop++;
        $pop = $pop-2;

        // We get the global configuration
        $cd = new \Modl\ConfigDAO();
        $config = $cd->get();

        $connected = (int)requestURL('http://localhost:1560/started/', 2);

        $infos = array(
                'url'           => BASE_URI,
                'language'      => $config->locale,
                'whitelist'     => $config->xmppwhitelist,
                'timezone'      => $config->timezone,
                'description'   => $config->description,
                'unregister'    => $config->unregister,
                'php_version'   => phpversion(),
                'version'       => APP_VERSION,
                'population'    => $pop,
                'linked'        => (int)requestURL('http://localhost:1560/linked/', 2),
                'started'       => $connected,
                'connected'     => $connected
            );

        $this->view->assign('json', json_encode($infos));
    }
}
