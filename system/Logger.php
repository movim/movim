<?php

/**
 * \brief Movim's logger class.
 *
 * Static class to be invoked for every debug log purpose in Movim.
 */
class Logger
{
    /*public static $logfilename = "log/movim.log";

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
    }*/
    
    private static $logs = array();
    
    public static function log($message) 
    {        
        self::addLog($message);
        
        openlog('movim', LOG_NDELAY, LOG_USER);
        $errlines = explode("\n",$message);
        foreach ($errlines as $txt) { syslog(LOG_DEBUG, trim($txt)); } 
        closelog();
    }
    
    public static function addLog($message)
    {
        array_push(self::$logs,$message);
    }
    
    public static function getLog()
    {
        return self::$logs;
    }
    
    public static function displayLog()
    {
        $logs = self::getLog();
        $html = '';
        if(!empty($logs)) {           
            $html = '<div class="message error">';
            foreach($logs as $l)
                $html .= $l.'<br />';
            $html .= '</div>';
        }
        
        return $html;
    }
}
