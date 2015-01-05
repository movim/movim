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
        //$this->addcss('notification.css');
        $this->addjs('notification.js');
        $this->registerEvent('pubsuberror', 'onPubsubError');
        $this->registerEvent('moxlerror', 'onMoxlError');
    }
    
    static function appendNotification($message, $type = 'info')
    {
        $id = md5($message.$type);

        switch($type) {
            case 'success':
                $icon = 'fa-check-circle';
                break;
            case 'info':
                $icon = 'fa-info-circle';
                break;
            case 'warning':
                $icon = 'fa-warning';
                break;
            case 'error':
                $icon = 'fa-times-circle';
                break;
            default:
                $icon = 'fa-info-circle';
                break;
        }
        
        /*$html = '
            <div class="notif notificationAnim '.$type.'" id="'.$id.'">
                <i class="fa '.$icon.'"></i> '.$message.'
            </div>';*/
        $html = $message;

        RPC::call('removeDiff', 'toast', $html, $id);
    }

    function onPubsubError($error) {
        Notification::appendNotification($error, 'error');
    }

    function onMoxlError($arr) {
        Notification::appendNotification($arr[1], 'error');
    }
}
