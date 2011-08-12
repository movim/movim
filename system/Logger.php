<?php

/**
 * \brief Movim's logger class.
 *
 * Static class to be invoked for every debug log purpose in Movim.
 */
class Logger
{
    public static $logfilename = "log/movim.log";

    // Predefined log levels
    const LOGLEVEL_CRITICAL = 0;
    const LOGLEVEL_ERROR    = 1;
    const LOGLEVEL_WARNING  = 2;
    const LOGLEVEL_INFO     = 3;
    const LOGLEVEL_STANDARD = 4;
    const LOGLEVEL_FINE     = 5;
    const LOGLEVEL_FINER    = 6;
    const LOGLEVEL_FINEST   = 7;

    public static function log($level, $message) {
        $server_loglevel = Conf::getServerConfElement('logLevel');

        if($server_loglevel >= $level) {
            if(!($lfp = fopen(BASE_PATH . self::$logfilename, 'a'))) {
                throw new MovimException(t("Cannot open log file '%s'", self::$logfilename));
            }

            fwrite($lfp, date('H:i:s').' '.$message."\n");
            fclose($lfp);
        }
    }
}
