<?php

require '../../vendor/autoload.php';

use Movim\Bootstrap;
use Movim\Controller\Front;

$bootstrap = new Bootstrap;
$bootstrap->boot();

$rqst = new Front;
$rqst->handle('picture');
