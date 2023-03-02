#!/usr/bin/env php

<?php

require dirname(__FILE__) . '/vendor/autoload.php';

use Symfony\Component\Console\Application;

$bootstrap = new Movim\Bootstrap;
$bootstrap->boot(true);

$application = new Application;
$application->add(new Movim\Console\ConfigCommand);
$application->add(new Movim\Console\DaemonCommand);
$application->add(new Movim\Console\EmojisToJsonCommand);
$application->add(new Movim\Console\CompileLanguages);
$application->add(new Movim\Console\CompileStickers);
$application->add(new Movim\Console\CompileOpcache);
$application->add(new Movim\Console\SetAdmin);
$application->run();
