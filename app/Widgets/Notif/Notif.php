<?php

namespace App\Widgets\Notif;

use Movim\Widget\Base;
use Movim\RPC;
use Carbon\Carbon;

use App\PushSubscription;
use App\User;
use App\Widgets\Chat\Chat;
use Movim\Widget\Wrapper;
use Moxl\Xec\Payload\Packet;

class Notif extends Base
{
    public $rpcCall = null;

    public function load()
    {
        $this->addjs('notif.js');
        $this->registerEvent('chat_counter', 'onChatCounter');
        $this->registerEvent('session_up', 'onSessionUp');
        $this->registerEvent('session_down', 'onSessionDown');
    }

    public function onSessionUp()
    {
        linker($this->sessionId)->session->delete('session_down');
    }

    public function onSessionDown()
    {
        linker($this->sessionId)->session->set('session_down', true);
    }

    public function onChatCounter(Packet $packet)
    {
        (new RPC(user: $this->me, sessionId: $this->sessionId))->call('Notif.counter', 'chat', $packet->content);
    }

    public function rpcCall($rpc)
    {
        $this->rpcCall = $rpc;
    }

    public function executeRPC()
    {
        if ($this->rpcCall) (new RPC(user: $this->me, sessionId: $this->sessionId))->call($this->rpcCall);
        $this->rpcCall = null;
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
    public function append(
        string $key,
        string $title,
        string $body,
        string $url,
        ?string $picture = null,
        ?int $time = 2,
        ?array $actions = [],
        ?array $data = [],
    ) {
        if ($picture == null) {
            $picture = BASE_URI . '/theme/img/app/128.png';
        }

        $data['url'] = $url;

        $session = linker($this->sessionId)->session;
        $notifs = $session->get('notifs');

        if ($session->get('session_down')) {
            requestPusher(
                userId: $this->me->id,
                tag: $key,
                title: $title,
                body: $body,
                picture: $picture,
                actions: $actions,
                data: $data,
            );
        } else {
            (new RPC(user: $this->me, sessionId: $this->sessionId))->call(
                'Notif.desktop',
                $key,
                time(),
                $title,
                $body,
                $picture,
                $actions,
                $data,
            );
        }

        $notifsKey = $session->get('notifs_key');

        if ($notifs == null) {
            $notifs = [];
        }

        $explode = explode('|', $key);
        $first = reset($explode);

        if (array_key_exists($first, $notifs)) {
            $count = $notifs[$first]++;

            // We re-append it
            unset($notifs[$first]);
            $notifs[$first] = $count;
        } else {
            $notifs[$first] = 1;
        }

        // Don't notify
        if ($notifsKey != null && $key == $notifsKey) {
            return;
        }

        if ($first === 'chat') {
            (new RPC(user: $this->me, sessionId: $this->sessionId))->call('Notif.counter', $first, $this->me->unreads(null, true));
            $this->executeRPC();
        } else {
            (new RPC(user: $this->me, sessionId: $this->sessionId))->call('Notif.counter', $first, $notifs[$first]);
            $this->executeRPC();
        }

        if ($first != $key) {
            if (array_key_exists($key, $notifs)) {
                $count = $notifs[$key]++;

                // We re-append it
                unset($notifs[$key]);
                $notifs[$key] = $count;
            } else {
                $notifs[$key] = 1;
            }

            (new RPC(user: $this->me, sessionId: $this->sessionId))->call('Notif.counter', $key, $notifs[$key]);
            $this->executeRPC();
        }

        if ($title != null) {
            (new RPC(user: $this->me, sessionId: $this->sessionId))->call(
                'Notif.snackbar',
                (new Notif(new User))->prepareSnackbar($title, $body, $picture, $url),
                $time
            );
        }

        $session->set('notifs', $notifs);

        Wrapper::getInstance()->iterate('notifs', (new Packet)->pack($notifs), user: $this->me, sessionId: $this->sessionId);
    }


    /**
     * @brief Get the current Notif key
     */
    public function getCurrent()
    {
        return linker($this->sessionId)->session->get('notifs_key');
    }

    /**
     * @brief Clear the counter of a key
     *
     * @param string $key The key to group the notifications
     * @return void
     */
    public function ajaxClear($key)
    {
        $session = linker($this->sessionId)->session;
        $notifs = $session->get('notifs');

        if ($notifs != null && array_key_exists($key, $notifs)) {
            $counter = $notifs[$key];
            unset($notifs[$key]);

            (new RPC(user: $this->me, sessionId: $this->sessionId))->call('Notif.counter', $key, 0);
            (new RPC(user: $this->me, sessionId: $this->sessionId))->call('Notif.clear', $key);

            $explode = explode('|', $key);
            $first = reset($explode);

            if (array_key_exists($first, $notifs)) {
                $notifs[$first] = $notifs[$first] - $counter;

                if ($notifs[$first] <= 0) {
                    unset($notifs[$first]);
                    (new RPC(user: $this->me, sessionId: $this->sessionId))->call('Notif.counter', $first, 0);
                } else {
                    (new RPC(user: $this->me, sessionId: $this->sessionId))->call('Notif.counter', $first, $notifs[$first]);
                }
            }
        }

        $session->set('notifs', $notifs);
        Wrapper::getInstance()->iterate('notifs_clear', (new Packet)->pack($key), sessionId: $this->sessionId);
    }

    /**
     * @brief Get all the keys
     * @return void
     */
    public function ajaxGet()
    {
        $notifs = linker($this->sessionId)->session->get('notifs');

        if ($notifs == null) $notifs = [];

        $notifs['chat'] = $this->me?->unreads() ?? 0;
        (new RPC(user: $this->me, sessionId: $this->sessionId))->call('Notif.refresh', $notifs);
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
        if (strpos($key, '|') !== false) (new Notif($this->me, sessionId: $this->sessionId))->ajaxClear($key);

        $session = linker($this->sessionId)->session;

        // If the page was blurred
        if ($session->get('notifs_key') === 'blurred') {
            (new Chat($this->me, sessionId: $this->sessionId))->onNotificationCounterClear((new Packet)->pack(explode('|', $key)));
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
        $this->dialog($view->draw('_notif_request'));
    }

    public function ajaxRequestGranted()
    {
        (new RPC(user: $this->me, sessionId: $this->sessionId))->call(
            'Notif.desktop',
            time(),
            $this->__('notification.request_title'),
            $this->__('notification.request_granted'),
            null,
            null,
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
        ?string $url = null,
    ) {
        $view = $this->tpl();

        $view->assign('title', $title);
        $view->assign('body', $body);
        $view->assign('picture', $picture);
        $view->assign('url', $url);

        return $view->draw('_notif');
    }
}
