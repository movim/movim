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

/**
* BOOTSTRAP
**/
define('DOCUMENT_ROOT',  dirname(__FILE__));
require_once(DOCUMENT_ROOT.'/system/Utils.php');
require_once(DOCUMENT_ROOT.'/system/Conf.php');
try {
    define('ENVIRONMENT',Conf::getServerConfElement('environment'));
} catch (Exception $e) {
//    define('ENVIRONMENT','production');//default environment is production
}
if (ENVIRONMENT === 'development') {
    ini_set('log_errors', 1);
    ini_set('display_errors', 0);
    ini_set('error_reporting', E_ALL );
    
} else {
    ini_set('log_errors', 1);
    ini_set('display_errors', 0);
    ini_set('error_reporting', E_ALL ^ E_DEPRECATED ^ E_NOTICE);
}
ini_set('error_log', DOCUMENT_ROOT.'/log/php.log');

// Run
require_once('init.php');

$polling = false;

$rqst = new ControllerMain();
$rqst->handle();

WidgetWrapper::getInstance(false);

// Closing stuff
WidgetWrapper::destroyInstance();
if (ENVIRONMENT === 'development') {
   
    print ' <div id="debug"><div class="carreful"><p>Be careful you are actually in development environment</p></div>';
    print '<div id="logs">';
    echo Logger::displayLog();
    print '</div></div>';
}
