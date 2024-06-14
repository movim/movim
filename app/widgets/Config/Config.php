<?php

use Movim\Widget\Base;

use Moxl\Xec\Action\Storage\Set;
use Moxl\Xec\Action\MAM\GetConfig;
use Moxl\Xec\Action\MAM\SetConfig;
use Moxl\Xec\Action\Pubsub\GetConfig as PubsubGetConfig;
use Moxl\Xec\Action\Pubsub\SetConfig as PubsubSetConfig;
use Respect\Validation\Validator;

class Config extends Base
{
    public function load()
    {
        $this->registerEvent('storage_set_handle', 'onConfig', 'conf');
        $this->registerEvent('mam_getconfig_handle', 'onMAMConfig', 'conf');
        $this->registerEvent('mam_setconfig_handle', 'onMAMConfigSaved', 'conf');
        $this->registerEvent('pubsub_getconfig_handle', 'onBlogConfig', 'conf');
        $this->registerEvent('pubsub_setconfig_handle', 'onBlogConfigSaved', 'conf');

        $this->addjs('config.js');
        $this->addcss('config.css');
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

    public function onBlogConfigSaved()
    {
        Toast::send($this->__('config.blog_saved'));
    }

    public function onBlogConfig($package)
    {
        $view = $this->tpl();

        $value = $package->content['config']->xpath('//field[@var=\'pubsub#access_model\']/value/text()');

        if (is_array($value)) {
            $view->assign('default', (string)$value[0]);
            $this->rpc('MovimTpl.fill', '#config_widget_blog', $view->draw('_config_blog'));
        }
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

    public function ajaxBlogGetConfig()
    {
        if ($this->user->hasPubsub()) {
            (new PubsubGetConfig)->setNode('urn:xmpp:microblog:0')->request();
        }
    }

    public function ajaxBlogSetConfig(stdClass $data)
    {
        if ($this->user->hasPubsub()) {
            $r = new PubsubSetConfig;
            $r->setNode('urn:xmpp:microblog:0')
              ->setData(formToArray($data))
              ->request();
        }
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
        $s->setData(serialize($config))
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

    public function ajaxChangePrivacy($value)
    {
        if ($value == true) {
            $this->user->setPublic();
            Toast::send($this->__('profile.public'));
        } else {
            $this->user->setPrivate();
            Toast::send($this->__('profile.restricted'));
        }
    }

    private function refreshConfig()
    {
        $this->rpc('MovimTpl.fill', '#config_widget_form', $this->prepareConfigForm());
    }

    public function display()
    {
        $this->view->assign('me', $this->user);
        $this->view->assign('form', $this->prepareConfigForm());
    }
}
