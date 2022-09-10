<?php

use Movim\Widget\Base;

use Moxl\Xec\Action\Storage\Set;

class NotificationConfig extends Base
{
    public function load()
    {
        $this->addjs('notificationconfig.js');
    }

    public function ajaxHttpPushGetConfig(?string $endpoint = null)
    {
        $pushSubscriptions = $this->user->pushSubscriptions;

        foreach ($pushSubscriptions as $pushSubscription) {
            $pushSubscription->self = ($pushSubscription->endpoint == $endpoint);
        }

        $pushSubscriptions = $pushSubscriptions->sortByDesc('self');

        $view = $this->tpl();
        $view->assign('pushSubscriptions', $pushSubscriptions);

        $this->rpc('MovimTpl.fill', '#notificationconfig_widget_push', $view->draw('_notificationconfig_push'));
    }

    public function ajaxTogglePushConfig(int $id, bool $enabled)
    {
        $pushSubscription = $this->user->pushSubscriptions()->where('id', $id)->firstOrFail();
        $pushSubscription->enabled = $enabled;
        $pushSubscription->save();

        Toast::send($this->__($enabled ? 'notificationconfig.push_enabled' : 'notificationconfig.push_disabled'));
    }

    public function ajaxHttpRequest()
    {
        $view = $this->tpl();
        $this->rpc('MovimTpl.fill', '#notificationconfig_widget_request', $view->draw('_notificationconfig_request'));
    }

    public function ajaxAudioSubmit($data)
    {
        $config = [];
        foreach ($data as $key => $value) {
            $config[$key] = $value->value;
        }

        // The user is updated in the Config widget
        $s = new Set;
        $s->setXmlns('movim:prefs')
          ->setData(serialize($config))
          ->request();
    }

    public function display()
    {
        $this->view->assign('conf', $this->user);
    }
}
