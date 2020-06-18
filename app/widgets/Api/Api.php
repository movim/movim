<?php

use App\Configuration;
use Movim\Widget\Base;

class Api extends Base
{
    public function ajaxRegister()
    {
        $json = requestURL(
            MOVIM_API.'pods/register',
            3,
            ['url' => BASE_URI]
        );

        $json = json_decode($json);

        if (isset($json) && $json->status == 200) {
            $this->rpc('MovimUtils.reloadThis');
            Notification::toast($this->__('api.conf_updated'));
        }
    }

    public function ajaxUnregister()
    {
        $configuration = Configuration::get();

        $configuration->unregister = !$configuration->unregister;
        $configuration->save();

        $this->rpc('MovimUtils.reloadThis');
    }

    public function display()
    {
        $this->view->assign(
            'infos',
            $this->__(
                'api.info',
                '<a href="http://api.movim.eu/" target="_blank">',
                '</a>'
            )
        );

        $json = requestURL(MOVIM_API.'pods/status', 2, ['url' => BASE_URI]);
        $json = json_decode($json);

        $configuration = Configuration::get();

        if (isset($json)) {
            $this->view->assign('json', $json);
            if ($json->status == 200) {
                $this->view->assign('unregister_status', $configuration->unregister);
            } else {
                $configuration->unregister = false;
                $configuration->save();
            }
        } else {
            $this->view->assign('json', null);
        }
    }
}
