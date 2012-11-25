<?php

/**
 * @file index.php
 * This file is part of MOVIM.
 *
 * @brief Prepares all the needed fixtures and fires up the main request
 * handler.
 *
 * @author Movim Project <movim@movim.eu>
 *
 * @version 0.4
 * @date 30 September 2010
 *
 * Copyright (C)2010 Movim Project
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @mainpage
 *
 * Movim is an XMPP-based communication platform. It uses a widget-based UI
 * system. A widget is a combination of server-side and client-side scripts that
 * interact though a custom xmlrpc protocol.
 *
 * Movim's core is designed to ease the implementation of XMPP web-based clients,
 * using massively asynchronous javascript and abstracting XMPP calls into an
 * events-based API.
 */


ini_set('log_errors', 0);
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL ^ E_DEPRECATED ^ E_NOTICE);

// If the configuration doesn't exist, run the installer.
if(!file_exists("config/conf.xml")) {
    header('Location: admin/'); exit;
} else {
    // Run
    require('init.php');

    $polling = false;
    $rqst = new ControllerMain();
    $rqst->handle();

    $widgets = WidgetWrapper::getInstance(false);
    $widgets->iterateCached('saveCache');

    // Closing stuff
    WidgetWrapper::destroyInstance();
    global $sdb;

    $sdb->close();
}
?>
