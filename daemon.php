#!/usr/bin/env php

<?php

require dirname(__FILE__) . '/vendor/autoload.php';

use Movim\Bootstrap;
use Movim\Console\DaemonCommand;
use Movim\Console\ConfigCommand;
use Movim\Console\EmojisToJsonCommand;
use Movim\Console\CompileLanguages;
use Symfony\Component\Console\Application;

$bootstrap = new Bootstrap;
$bootstrap->boot($argv[1] == 'help');

$application = new Application;
$application->add(new ConfigCommand);
$application->add(new DaemonCommand);
$application->add(new EmojisToJsonCommand);
$application->add(new CompileLanguages);
$application->run();
