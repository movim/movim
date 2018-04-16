#!/usr/bin/env php

<?php
require dirname(__FILE__) . '/vendor/autoload.php';

use Movim\Bootstrap;
use Movim\Console\DaemonCommand;
use Movim\Console\ConfigCommand;
use Symfony\Component\Console\Application;

$bootstrap = new Bootstrap;
$bootstrap->boot();

$application = new Application;
$application->add(new DaemonCommand);
$application->add(new ConfigCommand);
$application->run();

