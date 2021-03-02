<?php

use App\Configuration;
use Movim\Widget\Base;

class Subscribe extends Base
{
    public function accountNext($server)
    {
        return $this->route('accountnext', [$server]);
    }

    public function display()
    {
        $json = requestURL(MOVIM_API.'servers', 3, false, true);
        $json = json_decode($json);
        $config = Configuration::get();
        $this->view->assign('config', $config);

        if (is_object($json) && $json->status == 200) {
            $this->view->assign('servers', array_filter(
                (array)$json->servers,
                function ($server) use ($config) {
                    return empty($config->xmppwhitelist) || in_array($server->domain, $config->xmppwhitelist);
                }
            ));
        }
    }
}
