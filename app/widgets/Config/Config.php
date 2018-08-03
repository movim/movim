<?php

use Moxl\Xec\Action\Storage\Set;
use Moxl\Xec\Action\MAM\GetConfig;
use Moxl\Xec\Action\MAM\SetConfig;
use Respect\Validation\Validator;
use App\User;

class Config extends \Movim\Widget\Base
{
    function load()
    {
        $this->registerEvent('storage_set_handle', 'onConfig');
        $this->registerEvent('mam_getconfig_handle', 'onMAMConfig');
        $this->registerEvent('mam_setconfig_handle', 'onMAMConfigSaved');

        $this->addjs('config.js');
    }

    function prepareConfigForm()
    {
        $view = $this->tpl();

        $l = Movim\i18n\Locale::start();

        $view->assign('languages', $l->getList());
        $view->assign('conf',      User::me());

        $view->assign('submit',
            $this->call(
                'ajaxSubmit',
                "MovimUtils.formToJson('general')"
            )
            . "this.className='button color orange inactive oppose';
                this.onclick=null;"
        );

        return $view->draw('_config_form');
    }

    function onConfig($package)
    {
        $this->user->setConfig($package->content);
        $this->user->save();

        $this->refreshConfig();

        Notification::append(null, $this->__('config.updated'));
    }

    function onMAMConfig($package)
    {
        $view = $this->tpl();
        $view->assign('default', $package->content);
        $this->rpc('MovimTpl.fill', '#config_widget_mam', $view->draw('_config_mam'));
    }

    function onMAMConfigSaved()
    {
        Notification::append(null, $this->__('config.mam_saved'));
    }

    function ajaxMAMGetConfig()
    {
        if ($this->user->hasMAM()) {
            (new GetConfig)->request();
        }
    }

    function ajaxMAMSetConfig($value)
    {
        $s = new SetConfig;
        $s->setDefault($value)
          ->request();
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
        $this->rpc('MovimTpl.fill', '#config_widget', $this->prepareConfigForm());
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
