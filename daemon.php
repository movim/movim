#!/usr/bin/env php

<?php

require dirname(__FILE__) . '/vendor/autoload.php';

use Symfony\Component\Console\Application;

$bootstrap = new Movim\Bootstrap;
$bootstrap->boot(true);

$application = new Application;
$application->addCommand(new Movim\Console\ClearTemplatesCache);
$application->addCommand(new Movim\Console\CompileLanguages);
$application->addCommand(new Movim\Console\CompileOpcache);
$application->addCommand(new Movim\Console\CompileStickers);
$application->addCommand(new Movim\Console\ConfigCommand);
$application->addCommand(new Movim\Console\DaemonCommand);
$application->addCommand(new Movim\Console\EmojisToJsonCommand);
$application->addCommand(new Movim\Console\ImportEmojisPack);
$application->addCommand(new Movim\Console\SetAdmin);
$application->run();
