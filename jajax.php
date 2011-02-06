<?php

/**
 * @file jajax.php
 * This file is part of MOVIM.
 * 
 * @brief This is movim's ajax server.
 *
 * @author Etenil <etenil@etenilsrealm.nl>
 *
 * @version 1.0
 * @date  7 November 2010
 *
 * Copyright (C)2010 MOVIM team
 * 
 * See the file `COPYING' for licensing information.
 */

set_time_limit(3);

require("init.php");

$polling = true;

$ajax = new Ajaxer();
$ajax->handle();
?>
