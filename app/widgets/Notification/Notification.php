<?php

use Movim\Widget\Base;
use Movim\RPC;
use Movim\Session;

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

use App\PushSubscription;

class Notification extends Base
{
    public static $rpcCall = null;
    public function load()
    {
        $this->addjs('notification.js');
        $this->registerEvent('chat_counter', 'onChatCounter');
        $this->registerEvent('session_up', 'onSessionUp');
        $this->registerEvent('session_down', 'onSessionDown');
    }

    public function onSessionUp()
    {
        Session::start()->remove('session_down');
    }

    public function onSessionDown()
    {
        Session::start()->set('session_down', true);
    }

    public function onChatCounter(int $count = 0)
    {
        RPC::call('Notification.counter', 'chat', $count);
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
        $title = null,
        $body = null,
        $picture = null,
        $time = 2,
        $action = null,
        $group = null,
        $execute = null
    ) {
        if ($picture == null) {
            $picture = BASE_URI . '/theme/img/app/128.png';
        }

        $session = Session::start();
        $notifs = $session->get('notifs');

        if ($title != null) {
            $webPush = null;

            if (Session::start()->get('session_down')) {
                $keys = json_decode(file_get_contents(CACHE_PATH . 'vapid_keys.json'));

                $webPush = new WebPush([
                    'VAPID' => [
                        'subject' => 'https://movim.eu',
                        'publicKey' => $keys->publicKey,
                        'privateKey' => $keys->privateKey
                    ]
                ]);
            }

            // Push notification
            if ($webPush) {
                foreach (\App\User::me()->pushSubscriptions()->where('enabled', true)->get() as $pushSubscription) {
                    $subscription = Subscription::create([
                        'endpoint' => $pushSubscription->endpoint,
                        'contentEncoding' => 'aesgcm',
                        'keys' => [
                            'auth' => $pushSubscription->auth,
                            'p256dh' => $pushSubscription->p256dh
                        ]
                    ]);

                    $webPush->sendOneNotification(
                        $subscription,
                        json_encode([
                            'title' => $title,
                            'body' => $body,
                            'picture' => $picture,
                            'action' => $action,
                            'group' => $group,
                            'execute' => $execute,
                            'button' => __('button.open')
                        ])
                    );
                }

                // Normal notification
            } else {
                RPC::call('Notification.desktop', $title, $body, $picture, $action, $execute);
            }
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
            RPC::call('Notification.counter', $first, (\App\User::me())->unreads(null, true));
            self::executeRPC();
        } else {
            RPC::call('Notification.counter', $first, $notifs[$first]);
            self::executeRPC();
        }

        if ($first != $key) {
            if (array_key_exists($key, $notifs)) {
                $notifs[$key]++;
            } else {
                $notifs[$key] = 1;
            }

            RPC::call('Notification.counter', $key, $notifs[$key]);
            self::executeRPC();
        }

        if ($title != null) {
            $n = new Notification;
            RPC::call(
                'Notification.snackbar',
                $n->prepareSnackbar($title, $body, $picture, $action, $execute),
                $time
            );
        }

        $session->set('notifs', $notifs);
    }


    /**
     * @brief Get the current Notification key
     */
    public function getCurrent()
    {
        $session = Session::start();
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
        $session = Session::start();
        $notifs = $session->get('notifs');

        if ($notifs != null && array_key_exists($key, $notifs)) {
            $counter = $notifs[$key];
            unset($notifs[$key]);

            RPC::call('Notification.counter', $key, 0);

            $explode = explode('|', $key);
            $first = reset($explode);

            if (array_key_exists($first, $notifs)) {
                $notifs[$first] = $notifs[$first] - $counter;

                if ($notifs[$first] <= 0) {
                    unset($notifs[$first]);
                    RPC::call('Notification.counter', $first, 0);
                } else {
                    RPC::call('Notification.counter', $first, $notifs[$first]);
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
        $session = Session::start();
        $notifs = $session->get('notifs');

        if ($notifs == false) $notifs = [];

        $notifs['chat'] = (\App\User::me())->unreads();
        RPC::call('Notification.refresh', $notifs);
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
        if (strpos($key, '|') !== false) (new Notification)->ajaxClear($key);

        $session = Session::start();

        // If the page was blurred
        if ($session->get('notifs_key') === 'blurred') {
            $this->event('notification_counter_clear', explode('|', $key));
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
        $pushSubscription = $this->user->pushSubscriptions()->where('endpoint', $endpoint)->first();

        $p = $pushSubscription ?? new PushSubscription;
        $p->user_id = $this->user->id;
        $p->endpoint = $endpoint;
        $p->auth = $auth;
        $p->p256dh = $p256dh;

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
        $pushSubscription = $this->user->pushSubscriptions()->where('endpoint', $endpoint)->firstOrFail();
        $pushSubscription->touch();
    }

    /**
     * @brief Request user permission to show notifications
     */
    public function ajaxRequest()
    {
        $view = $this->tpl();
        Dialog::fill($view->draw('_notification_request'));
    }

    public function ajaxRequestGranted()
    {
        RPC::call('Notification.desktop',
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
        Toast::send($this->__('notification.request_denied'));
    }

    private function prepareSnackbar($title, $body = null, $picture = null, $action = null, $execute = null)
    {
        $view = $this->tpl();

        $view->assign('title', $title);
        $view->assign('body', $body);
        $view->assign('picture', $picture);
        $view->assign('action', $action);
        $view->assign('onclick', $execute);

        return $view->draw('_notification');
    }
}
