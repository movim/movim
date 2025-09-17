<?php

namespace App\Widgets\NotificationConfig;

use App\Widgets\Toast\Toast;
use Movim\Widget\Base;

class NotificationConfig extends Base
{
    public function load()
    {
        $this->addjs('notificationconfig.js');
    }

    public function ajaxHttpPushGetConfig(?string $endpoint = null)
    {
        $pushSubscriptions = config('database.driver') == 'mysql'
            ? $this->me->pushSubscriptions()->orderByRaw('(activity_at is null), activity_at desc')->get()
            : $this->me->pushSubscriptions()->orderByRaw('activity_at desc nulls last')->get();

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
        $pushSubscription = $this->me->pushSubscriptions()->where('id', $id)->firstOrFail();
        $pushSubscription->enabled = $enabled;
        $pushSubscription->save();

        Toast::send($this->__($enabled ? 'notificationconfig.push_enabled' : 'notificationconfig.push_disabled'));
    }

    public function ajaxHttpRequest()
    {
        $view = $this->tpl();
        $this->rpc('MovimTpl.fill', '#notificationconfig_widget_request', $view->draw('_notificationconfig_request'));
    }
}
