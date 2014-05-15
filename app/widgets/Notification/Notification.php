<?php

/**
 * @package Widgets
 *
 * @file Notifs.php
 * This file is part of MOVIM.
 *
 * @brief The notification widget
 *
 * @author Timothée Jaussoin <edhelas@gmail.com>
 *
 * @version 1.0
 * @date 16 juin 2011
 *
 * Copyright (C)2010 MOVIM project
 *
 * See COPYING for licensing information.
 */

class Notification extends WidgetCommon
{
    function load()
    {
        $this->addcss('notification.css');
        $this->addjs('notification.js');
        $this->registerEvent('pubsuberror', 'onPubsubError');
        $this->registerEvent('moxlerror', 'onMoxlError');
    }
    
    static function appendNotification($message, $type = 'info')
    {
        $id = md5($message.$type);
        $html = '
            <div class="notif notificationAnim '.$type.'" id="'.$id.'">'.
                $message.'
            </div>';

        RPC::call('removeDiff', 'notification_widget', $html);
        RPC::commit();
    }

    function onPubsubError($error) {
        Notification::appendNotification($error, 'error');
    }

    function onMoxlError($arr) {
        Notification::appendNotification($arr[1], 'error');
    }
}
