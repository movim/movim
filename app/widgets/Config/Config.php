<?php

use Movim\Widget\Base;

use Moxl\Xec\Action\Storage\Set;
use Moxl\Xec\Action\MAM\GetConfig;
use Moxl\Xec\Action\MAM\SetConfig;

use Respect\Validation\Validator;

class Config extends Base
{
    public function load()
    {
        $this->registerEvent('storage_set_handle', 'onConfig');
        $this->registerEvent('mam_getconfig_handle', 'onMAMConfig');
        $this->registerEvent('mam_setconfig_handle', 'onMAMConfigSaved');

        $this->addjs('config.js');
    }

    public function prepareConfigForm()
    {
        $view = $this->tpl();

        $l = Movim\i18n\Locale::start();

        $view->assign('languages', $l->getList());
        $view->assign('conf', $this->user);

        return $view->draw('_config_form');
    }

    public function onConfig($package)
    {
        $this->user->setConfig($package->content);
        $this->user->save();

        $this->refreshConfig();

        Toast::send($this->__('config.updated'));
    }

    public function onMAMConfig($package)
    {
        $view = $this->tpl();
        $view->assign('default', $package->content);
        $this->rpc('MovimTpl.fill', '#config_widget_mam', $view->draw('_config_mam'));
    }

    public function onMAMConfigSaved()
    {
        Toast::send($this->__('config.mam_saved'));
    }

    public function ajaxMAMGetConfig()
    {
        if ($this->user->hasMAM()) {
            (new GetConfig)->request();
        }
    }

    public function ajaxMAMSetConfig($value)
    {
        $s = new SetConfig;
        $s->setDefault($value)
          ->request();
    }

    public function ajaxSubmit($data)
    {
        if (!validateForm($data)) {
            $this->refreshConfig();
            Toast::send($this->__('config.not_valid'));
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

    public function ajaxEditNickname()
    {
        $view = $this->tpl();
        $view->assign('me', $this->user);
        Dialog::fill($view->draw('_config_nickname'));
    }

    public function ajaxSaveNickname(string $nickname)
    {
        if (Validator::regex('/^[a-z_\-\d]{3,64}$/i')->validate($nickname)) {
            if (\App\User::where('nickname', $nickname)->where('id', '!=', $this->user->id)->first()) {
                Toast::send($this->__('profile.nickname_conflict'));
                return;
            }

            $this->user->nickname = $nickname;
            $this->user->save();
            $this->refreshConfig();

            (new Dialog)->ajaxClear();
            Toast::send($this->__('profile.nickname_saved'));
        } else {
            Toast::send($this->__('profile.nickname_error'));
        }
    }

    private function refreshConfig()
    {
        $this->rpc('MovimTpl.fill', '#config_widget_form', $this->prepareConfigForm());
    }

    public function display()
    {
        $this->view->assign('form', $this->prepareConfigForm());
    }
}
