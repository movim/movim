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

require '../vendor/autoload.php';

use Movim\Bootstrap;
use Movim\Controller\Front;
use Movim\Widget\Wrapper;

$bootstrap = new Bootstrap;
$bootstrap->boot();

$rqst = new Front;
$rqst->handle();

Wrapper::getInstance(false);
Wrapper::destroyInstance();
