<?php

use App\Configuration;
use Movim\Widget\Base;

class Subscribe extends Base
{
    function flagPath($country)
    {
        return BASE_URI.'themes/material/img/flags/'.strtolower($country).'.png';
    }

    function accountNext($server)
    {
        return $this->route('accountnext', [$server]);
    }

    function display()
    {
        $json = requestURL(MOVIM_API.'servers', 3, false, true);
        $json = json_decode($json);
        $this->view->assign('config', Configuration::findOrNew(1));

        if (is_object($json) && $json->status == 200) {
            $this->view->assign('servers', $json->servers);
        }
    }
}
