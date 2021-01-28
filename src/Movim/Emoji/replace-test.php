<?php
/*
 * SPDX-FileCopyrightText: 2010 Jaussoin Timothee 
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
define('BASE_URI', '(base)');
mb_internal_encoding("UTF-8");

require_once '../Emoji.php';

$text = file_get_contents('php://stdin');
echo \Movim\Emoji::getInstance()->replace($text);
