<?php

class Api extends \Movim\Widget\Base
{
    function ajaxRegister()
    {
        $json = requestURL(
            MOVIM_API.'pods/register',
            3,
            ['url' => BASE_URI]);

        $json = json_decode($json);

        if(isset($json) && $json->status == 200) {
            $this->rpc('MovimUtils.reloadThis');
            Notification::append(null, $this->__('api.conf_updated'));
        }
    }

    function ajaxUnregister()
    {
        $cd = new \Modl\ConfigDAO;
        $config = $cd->get();

        $config->unregister = !$config->unregister;
        $cd->set($config);

        $this->rpc('MovimUtils.reloadThis');
    }

    function display()
    {
        $this->view->assign(
            'infos',
            $this->__(
                'api.info',
                '<a href="http://api.movim.eu/" target="_blank">',
                '</a>'));

        $json = requestURL(MOVIM_API.'pods/status', 2, ['url' => BASE_URI]);
        $json = json_decode($json);

        $cd = new \Modl\ConfigDAO();
        $config = $cd->get();

        if(isset($json)) {
            $this->view->assign('json', $json);
            if($json->status == 200) {
                $this->view->assign('unregister', $this->call('ajaxUnregister'));
                $this->view->assign('unregister_status', $config->unregister);
            } else {
                $config->unregister = false;
                $cd->set($config);
                $this->view->assign('register', $this->call('ajaxRegister'));
            }
        } else {
            $this->view->assign('json', null);
        }
    }
}
