#!/usr/bin/env php

<?php

require dirname(__FILE__).'/vendor/autoload.php';

use Movim\Bootstrap;
use Movim\Console\ClearImagesCache;
use Movim\Console\ClearTemplatesCache;
use Movim\Console\CompileLanguages;
use Movim\Console\CompileOpcache;
use Movim\Console\CompileStickers;
use Movim\Console\ConfigCommand;
use Movim\Console\DaemonCommand;
use Movim\Console\EmojisToJsonCommand;
use Movim\Console\GalenerManager;
use Movim\Console\ImportEmojisPack;
use Movim\Console\SessionsTree;
use Movim\Console\SetAdmin;
use Symfony\Component\Console\Application;

$bootstrap = new Bootstrap;
$bootstrap->boot(true);

$application = new Application;
$application->addCommand(new ClearImagesCache);
$application->addCommand(new ClearTemplatesCache);
$application->addCommand(new CompileLanguages);
$application->addCommand(new CompileOpcache);
$application->addCommand(new CompileStickers);
$application->addCommand(new ConfigCommand);
$application->addCommand(new DaemonCommand);
$application->addCommand(new EmojisToJsonCommand);
$application->addCommand(new GalenerManager);
$application->addCommand(new ImportEmojisPack);
$application->addCommand(new SessionsTree);
$application->addCommand(new SetAdmin);
$application->run();
