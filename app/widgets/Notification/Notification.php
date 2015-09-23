<?php

class Notification extends WidgetBase
{
    function load()
    {
        $this->addjs('notification.js');
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
    static function append($key = null, $title, $body = null, $picture = null, $time = 2, $action = null)
    {
        // In this case we have an action confirmation
        if($key == null) {
            RPC::call('Notification.toast', $title);
            return;
        }

        $session = Session::start();
        $notifs = $session->get('notifs');

        RPC::call('Notification.desktop', $title, $body, $picture, $action);

        $notifs_key = $session->get('notifs_key');

        if($notifs == null) $notifs = array();

        $explode = explode('|', $key);
        $first = reset($explode);

        // What we receive is not what it's on the screen on Android
        if($key != null && $key != $notifs_key) {
            RPC::call('Notification.android', $title, $body, $picture, $action);
        }

        if(array_key_exists($first, $notifs)) {
            $notifs[$first]++;
        } else {
            $notifs[$first] = 1;
        }

        if($notifs_key != null && $key == $notifs_key) return;

        RPC::call('Notification.counter', $first, $notifs[$first]);

        if($first != $key) {
            if(array_key_exists($key, $notifs)) {
                $notifs[$key]++;
            } else {
                $notifs[$key] = 1;
            }

            RPC::call('Notification.counter', $key, $notifs[$key]);
        }

        $n = new Notification;
        RPC::call('Notification.snackbar', $n->prepareSnackbar($title, $body, $picture, $action), $time);

        $session->set('notifs', $notifs);
    }

    /**
     * @brief Clear the counter of a key
     *
     * @param string $key The key to group the notifications
     * @return void
     */
    function ajaxClear($key)
    {
        $session = Session::start();
        $notifs = $session->get('notifs');

        if($notifs != null && array_key_exists($key, $notifs)) {
            $counter = $notifs[$key];
            unset($notifs[$key]);

            RPC::call('Notification.counter', $key, '');

            $explode = explode('|', $key);
            $first = reset($explode);

            if(array_key_exists($first, $notifs)) {
                $notifs[$first] = $notifs[$first] - $counter;

                if($notifs[$first] <= 0) {
                    unset($notifs[$first]);
                    RPC::call('Notification.counter', $first, '');
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
    function ajaxGet()
    {
        $session = Session::start();
        $notifs = $session->get('notifs');
        if($notifs != null) RPC::call('Notification.refresh', $notifs);
    }

    /**
     * @brief Set the current used key (to prevent notifications on current view)
     *
     * @param string $key
     * @return void
     */
    function ajaxCurrent($key)
    {
        $session = Session::start();
        $session->set('notifs_key', $key);
    }

    function prepareSnackbar($title, $body = null, $picture = null, $action = null)
    {
        $view = $this->tpl();

        $view->assign('title', $title);
        $view->assign('body', $body);
        $view->assign('picture', $picture);
        $view->assign('action', $action);

        return $view->draw('_notification', true);
    }
}
