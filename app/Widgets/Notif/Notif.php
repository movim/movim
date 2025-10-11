<?php

namespace App\Widgets\Notif;

use Movim\Widget\Base;
use Movim\RPC;
use Movim\Session;
use Carbon\Carbon;

use App\PushSubscription;
use App\Widgets\Chat\Chat;
use App\Widgets\Dialog\Dialog;
use Moxl\Xec\Payload\Packet;

class Notif extends Base
{
    public static $rpcCall = null;
    public function load()
    {
        $this->addjs('notif.js');
        $this->registerEvent('chat_counter', 'onChatCounter');
        $this->registerEvent('session_up', 'onSessionUp');
        $this->registerEvent('session_down', 'onSessionDown');
    }

    public function onSessionUp()
    {
        Session::instance()->delete('session_down');
    }

    public function onSessionDown()
    {
        Session::instance()->set('session_down', true);
    }

    public function onChatCounter(Packet $packet)
    {
        RPC::call('Notif.counter', 'chat', $packet->content);
    }

    public static function rpcCall($rpc)
    {
        self::$rpcCall = $rpc;
    }

    public static function executeRPC()
    {
        if (self::$rpcCall) RPC::call(self::$rpcCall);
        self::rpcCall(null);
    }

    /**
     * @brief Notify something
     *
     * @param string $key The key to group the notifications
     * @param string $title The displayed title
     * @param string $body The displayed body
     * @param string $body The displayed URL
     * @param integer $time The displayed time (in secondes)
     * @param integer $action An action
     * @return void
     */
    public static function append(
        string $key,
        string $title,
        string $body,
        ?string $picture = null,
        ?int $time = 2,
        ?string $action = null,
        ?string $group = null,
        ?string $execute = null
    ) {
        if ($picture == null) {
            $picture = BASE_URI . '/theme/img/app/128.png';
        }

        $session = Session::instance();
        $notifs = $session->get('notifs');

        if (Session::instance()->get('session_down')) {
            requestPusher(
                me()->id,
                $title,
                $body,
                $picture,
                $action,
                $group,
                $execute
            );
        } else {
            RPC::call('Notif.desktop', $title, $body, $picture, $action, $execute);
        }

        $notifsKey = $session->get('notifs_key');

        if ($notifs == null) {
            $notifs = [];
        }

        $explode = explode('|', $key);
        $first = reset($explode);

        if (array_key_exists($first, $notifs)) {
            $notifs[$first]++;
        } else {
            $notifs[$first] = 1;
        }

        // Don't notify
        if ($notifsKey != null && $key == $notifsKey) {
            return;
        }

        if ($first === 'chat') {
            RPC::call('Notif.counter', $first, me()->unreads(null, true));
            self::executeRPC();
        } else {
            RPC::call('Notif.counter', $first, $notifs[$first]);
            self::executeRPC();
        }

        if ($first != $key) {
            if (array_key_exists($key, $notifs)) {
                $notifs[$key]++;
            } else {
                $notifs[$key] = 1;
            }

            RPC::call('Notif.counter', $key, $notifs[$key]);
            self::executeRPC();
        }

        if ($title != null) {
            $n = new Notif;
            RPC::call(
                'Notif.snackbar',
                $n->prepareSnackbar($title, $body, $picture, $action, $execute),
                $time
            );
        }

        $session->set('notifs', $notifs);
    }


    /**
     * @brief Get the current Notif key
     */
    public function getCurrent()
    {
        $session = Session::instance();
        return $session->get('notifs_key');
    }

    /**
     * @brief Clear the counter of a key
     *
     * @param string $key The key to group the notifications
     * @return void
     */
    public function ajaxClear($key)
    {
        $session = Session::instance();
        $notifs = $session->get('notifs');

        if ($notifs != null && array_key_exists($key, $notifs)) {
            $counter = $notifs[$key];
            unset($notifs[$key]);

            RPC::call('Notif.counter', $key, 0);

            $explode = explode('|', $key);
            $first = reset($explode);

            if (array_key_exists($first, $notifs)) {
                $notifs[$first] = $notifs[$first] - $counter;

                if ($notifs[$first] <= 0) {
                    unset($notifs[$first]);
                    RPC::call('Notif.counter', $first, 0);
                } else {
                    RPC::call('Notif.counter', $first, $notifs[$first]);
                }
            }
        }

        $session->set('notifs', $notifs);
    }

    /**
     * @brief Get all the keys
     * @return void
     */
    public function ajaxGet()
    {
        $session = Session::instance();
        $notifs = $session->get('notifs');

        if ($notifs == null) $notifs = [];

        $notifs['chat'] = (me())->unreads();
        RPC::call('Notif.refresh', $notifs);
    }

    /**
     * @brief Set the current used key (to prevent notifications on current view)
     *
     * @param string $key
     * @return void
     */
    public function ajaxCurrent($key)
    {
        // Clear the specific keys
        if (strpos($key, '|') !== false) (new Notif)->ajaxClear($key);

        $session = Session::instance();

        // If the page was blurred
        if ($session->get('notifs_key') === 'blurred') {
            (new Chat)->onNotificationCounterClear((new Packet)->pack(explode('|', $key)));
        }

        $session->set('notifs_key', $key);
    }

    /**
     * @brief Register a push notification subscription
     *
     * @param string $endpoint
     * @param string $auth
     * @param string $p256dh
     */
    public function ajaxRegisterPushSubscrition(string $endpoint, string $auth, string $p256dh, ?string $userAgent)
    {
        $pushSubscription = $this->me->pushSubscriptions()->where('endpoint', $endpoint)->first();

        $p = $pushSubscription ?? new PushSubscription;
        $p->user_id = $this->me->id;
        $p->endpoint = $endpoint;
        $p->auth = $auth;
        $p->p256dh = $p256dh;
        $p->activity_at = Carbon::now();

        if ($userAgent) {
            $p->browser = getBrowser($userAgent);
            $p->platform = getPlatform($userAgent);
        }

        $p->save();
    }

    /**
     * @brief Refresh a push notification subscription
     */
    public function ajaxHttpTouchPushSubscription(string $endpoint)
    {
        $pushSubscription = $this->me->pushSubscriptions()->where('endpoint', $endpoint)->first();

        if ($pushSubscription) {
            $pushSubscription->activity_at = Carbon::now();
            $pushSubscription->save();
        }
    }

    /**
     * @brief Request user permission to show notifications
     */
    public function ajaxRequest()
    {
        $view = $this->tpl();
        Dialog::fill($view->draw('_notif_request'));
    }

    public function ajaxRequestGranted()
    {
        RPC::call(
            'Notif.desktop',
            $this->__('notification.request_title'),
            $this->__('notification.request_granted'),
            null,
            null,
            null,
            true
        );
    }

    public function ajaxRequestDenied()
    {
        $this->toast($this->__('notification.request_denied'));
    }

    private function prepareSnackbar(
        string $title,
        string $body,
        ?string $picture = null,
        ?string $action = null,
        ?string $execute = null
    ) {
        $view = $this->tpl();

        $view->assign('title', $title);
        $view->assign('body', $body);
        $view->assign('picture', $picture);
        $view->assign('action', $action);
        $view->assign('onclick', $execute);

        return $view->draw('_notif');
    }
}
