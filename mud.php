#!/usr/bin/php

<?php
require __DIR__ . '/vendor/autoload.php';

use Movim\Bootstrap;
use Movim\Console\DatabaseCommand;
use Movim\Console\ConfigCommand;
use Symfony\Component\Console\Application;

$bootstrap = new Bootstrap;
$bootstrap->boot();

$application = new Application;
$application->add(new ConfigCommand);
$application->run();

