<?php

use Moxl\Xec\Action\Storage\Set;
use Respect\Validation\Validator;

class Config extends \Movim\Widget\Base
{
    function load()
    {
        $this->registerEvent('storage_set_handle', 'onConfig');
        $this->addjs('config.js');
    }

    function prepareConfigForm()
    {
        $view = $this->tpl();

        /* We load the user configuration */
        $sd = new \Modl\SettingDAO;
        $l = Movim\i18n\Locale::start();

        $view->assign('languages', $l->getList());
        $view->assign('me',        $this->user->getLogin());
        $view->assign('conf',      $sd->get());

        $view->assign('submit',
            $this->call(
                'ajaxSubmit',
                "MovimUtils.formToJson('general')"
            )
            . "this.className='button color orange inactive oppose';
                this.onclick=null;"
        );

        return $view->draw('_config_form', true);
    }

    function onConfig($package)
    {
        $data = (array)$package->content;
        $this->user->setConfig($data);

        $this->refreshConfig();

        Notification::append(null, $this->__('config.updated'));
    }

    function ajaxSubmit($data)
    {
        if (!$this->validateForm($data)) {
            $this->refreshConfig();
            Notification::append(null, $this->__('config.not_valid'));
            return;
        }

        $config = [];
        foreach ($data as $key => $value) {
            $config[$key] = $value->value;
        }

        $s = new Set;
        $s->setXmlns('movim:prefs')
          ->setData(serialize($config))
          ->request();
    }

    private function refreshConfig()
    {
        $html = $this->prepareConfigForm();

        $this->rpc('MovimTpl.fill', '#config_widget', $html);
        $this->rpc('Config.load');
    }

    private function validateForm($data)
    {
        $l = Movim\i18n\Locale::start();

        return (Validator::in(array_keys($l->getList()))->validate($data->language->value)
            && ($data->cssurl->value == '' || Validator::url()->validate($data->cssurl->value)));
    }

    function display()
    {
        $this->view->assign('form', $this->prepareConfigForm());
    }
}
