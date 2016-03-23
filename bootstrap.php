<?php
if (!defined('DOCUMENT_ROOT')) die('Access denied');

require 'vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\SyslogHandler;

/**
 * Error Handler...
 */
function systemErrorHandler($errno, $errstr, $errfile, $errline, $errcontext = null)
{
    $log = new Logger('movim');
    $log->pushHandler(new SyslogHandler('movim'));
    $log->addError($errstr);
    return false;
}

function fatalErrorShutdownHandler()
{
    $last_error = error_get_last();
    if($last_error['type'] === E_ERROR) {
        systemErrorHandler(
            E_ERROR,
            $last_error['message'],
            $last_error['file'],
            $last_error['line']);

        if (ob_get_contents()) ob_clean();

        echo "Oops... something went wrong.\n";
        echo "But don't panic. The NSA is on the case.\n";

        if (ob_get_contents()) ob_end_clean();
    }
}

