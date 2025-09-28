<?php

namespace App\Widgets\Config;

use App\Post;
use App\User;
use App\Widgets\Dialog\Dialog;
use App\Widgets\Presence\Presence;
use App\Widgets\Toast\Toast;
use Movim\i18n\Locale;
use Movim\Widget\Base;

use Moxl\Xec\Action\Storage\Set;
use Moxl\Xec\Action\MAM\GetConfig;
use Moxl\Xec\Action\MAM\SetConfig;
use Moxl\Xec\Action\Pubsub\GetConfig as PubsubGetConfig;
use Moxl\Xec\Action\Pubsub\SetConfig as PubsubSetConfig;
use Moxl\Xec\Payload\Packet;
use Respect\Validation\Validator;

class Config extends Base
{
    public function load()
    {
        $this->registerEvent('storage_set_handle', 'onConfig', 'configuration');
        $this->registerEvent('mam_getconfig_handle', 'onMAMConfig', 'configuration');
        $this->registerEvent('mam_setconfig_handle', 'onMAMConfigSaved', 'configuration');
        $this->registerEvent('pubsub_getconfig_handle', 'onBlogConfig', 'configuration');
        $this->registerEvent('pubsub_setconfig_handle', 'onBlogConfigSaved', 'configuration');

        $this->addjs('config.js');
        $this->addcss('config.css');
    }

    public function prepareConfigForm()
    {
        $view = $this->tpl();

        $l = Locale::start();

        $view->assign('languages', $l->getList());
        $view->assign('accent_colors', User::ACCENT_COLORS);
        $view->assign('configuration', $this->me);

        return $view->draw('_config_form');
    }

    public function onConfig(Packet $packet)
    {
        $this->me->setConfig($packet->content);
        $this->me->save();

        $this->refreshConfig();

        Toast::send($this->__('config.updated'));
    }

    public function onMAMConfig(Packet $packet)
    {
        $view = $this->tpl();
        $view->assign('default', $packet->content);
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

    public function onBlogConfig(Packet $packet)
    {
        $view = $this->tpl();

        $value = $packet->content['config']->xpath('//field[@var=\'pubsub#access_model\']/value/text()');

        if (is_array($value)) {
            $view->assign('default', (string)$value[0]);
            $this->rpc('MovimTpl.fill', '#config_widget_blog', $view->draw('_config_blog'));
        }
    }

    public function ajaxMAMGetConfig()
    {
        if ($this->me->hasMAM()) {
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
        if ($this->me->hasPubsub()) {
            (new PubsubGetConfig)->setNode(Post::MICROBLOG_NODE)->request();
        }
    }

    public function ajaxBlogSetConfig(\stdClass $data)
    {
        if ($this->me->hasPubsub()) {
            $r = new PubsubSetConfig;
            $r->setNode(Post::MICROBLOG_NODE)
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

            if (in_array($key, ['notificationcall', 'notificationchat'])) {
                $this->updateSystemVariable($key, $value->value);
            }
        }

        $s = new Set;
        $s->setData($config)
            ->request();
    }

    public function ajaxEditNickname()
    {
        $view = $this->tpl();
        $view->assign('me', $this->me);
        Dialog::fill($view->draw('_config_nickname'));
    }

    public function ajaxSaveNickname(string $nickname)
    {
        if (Validator::regex('/^[a-z_\-\d]{3,64}$/i')->isValid($nickname)) {
            if (\App\User::where('nickname', $nickname)->where('id', '!=', $this->me->id)->first()) {
                Toast::send($this->__('profile.nickname_conflict'));
                return;
            }

            $this->me->nickname = $nickname;
            $this->me->save();
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
            $this->me->setPublic();
            Toast::send($this->__('profile.public'));
        } else {
            $this->me->setPrivate();
            Toast::send($this->__('profile.restricted'));
        }
    }

    private function refreshConfig()
    {
        $this->rpc('MovimTpl.fill', '#config_widget_form', $this->prepareConfigForm());
    }

    public function updateSystemVariable(string $variable, $value)
    {
        match ($variable) {
            'notificationcall' => $this->rpc('Config.updateSystemVariable', 'NOTIFICATION_CALL', (bool)$value),
            'notificationchat' => $this->rpc('Config.updateSystemVariable', 'NOTIFICATION_CHAT', (bool)$value),
        };
    }

    public function prepareAccentColorRadio(string $color)
    {
        $view = $this->tpl();
        $view->assign('configuration', $this->me);
        $view->assign('color', $color);

        return $view->draw('_config_accent_color');
    }

    public function display()
    {
        $this->view->assign('me', $this->me);
        $this->view->assign('form', $this->prepareConfigForm());
    }
}
