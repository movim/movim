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
 * @version 1.0
 * @date 13 October 2010
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
 *
 */

ini_set('log_errors', 0);
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL ^ E_DEPRECATED ^ E_NOTICE);

require_once('init.php');

$polling = false;

$conf = new GetConf();
if($conf->getServerConfElement('install') == 1) {
	require_once('install.php');
} else {
	// Run
	$rqst = new Dispatcher();
	$rqst->handle();
}
?>
