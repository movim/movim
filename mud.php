#!/usr/bin/php
<?php
require __DIR__ . '/vendor/autoload.php';

define('DOCUMENT_ROOT', dirname(__FILE__));

use Movim\Bootstrap;
use Movim\Console\DatabaseCommand;
use Movim\Console\ConfigCommand;
use Symfony\Component\Console\Application;

$bootstrap = new Bootstrap();
$bootstrap->boot();

$application = new Application;
$application->add(new DatabaseCommand);
$application->add(new ConfigCommand);
$application->run();

