#!/usr/bin/env php

<?php
require dirname(__FILE__) . '/vendor/autoload.php';

define('DOCUMENT_ROOT', dirname(__FILE__));

use Movim\Bootstrap;
use Movim\Console\DaemonCommand;
use Symfony\Component\Console\Application;

$bootstrap = new Bootstrap;
$bootstrap->boot();

$daemon = new DaemonCommand;
$application = new Application;
$application->add($daemon);
$application->run();

