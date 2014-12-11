<?php

/**
 * @file index.php
 * This file is part of Movim.
 *
 * @brief Prepares all the needed fixtures and fires up the main request
 * handler.
 *
 * @author Movim Project <contact@movim.eu>
 *
 * Copyright (C)2013 Movim Project
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

define('DOCUMENT_ROOT', dirname(__FILE__));
require_once(DOCUMENT_ROOT.'/bootstrap.php');

use Monolog\Logger;
use Monolog\Handler\SyslogHandler;

try {
    if((isset($_GET['q']) && $_GET['q'] == 'admin') ||
       (isset($_GET['query']) && $_GET['query'] == 'admin')
      )
        define('FAIL_SAFE', true);
    else
        define('FAIL_SAFE', false);
    
    $bootstrap = new Bootstrap();
    
    $bootstrap->boot();

    $rqst = new FrontController();
    $rqst->handle();

    WidgetWrapper::getInstance(false);
    // Closing stuff
    WidgetWrapper::destroyInstance();

} catch (Exception $e) {
    $log = new Logger('movim');
    $log->pushHandler(new SyslogHandler('movim'));
    $log->addInfo($e->getMessage());
    
    if (ENVIRONMENT === 'development' && !FAIL_SAFE) {
        ?>
            <div id="final_exception" class="error debug">
                <h2>An error happened</h2>
                <p><?php print $e->getMessage();?></p>
            </div>
        <?php
    } elseif(!FAIL_SAFE) {
        ?>
        <div class="carreful">
            <h2> Oops... something went wrong.</h2>
            <p>But don't panic. The NSA is on the case.</p>
        </div>
        <?php
    }
    
    if(FAIL_SAFE) {
        $r = new Route;
        $rqst = new FrontController();
        $rqst->handle();
    }
} 
