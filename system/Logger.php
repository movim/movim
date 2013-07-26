<?php

if (!defined('ROOTDIR')) {
    die('Error 57895');
}
if (!is_dir(ROOTDIR)) {
    die('Error 57896');
}

/**
 * \brief Movim's logger class.
 *
 * Static class to be invoked for every debug log purpose in Movim.
 */
abstract class Logger
{

    /* public static $logfilename = "log/movim.log";

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
      } */

    private static $logs = array();

    public static function log($message)
    {
        self::addLog($message);

        /*openlog('movim', LOG_NDELAY, LOG_USER);
        $errlines = explode("\n", $message);
        foreach ($errlines as $txt) {
            syslog(LOG_DEBUG, trim($txt));
        }
        closelog();*/

    }

    public static function addLog($message)
    {
        array_push(self::$logs, $message);

    }

    /**
     * getter logs
     * @return array
     */
    public static function getLog()
    {
        return self::$logs;

    }

    public static function displayLog()
    {
        $logs = self::getLog();
        $html = '';
        if (!empty($logs)) {
            $html = '<div class="message error">';
            foreach ($logs as $l) $html .= $l . '<br />';
            $html .= '</div>';
        }
        return $html;

    }

    public static function getInlineLogs()
    {
        $logs = self::getLog();
        $txt = '';
        foreach ($logs as $l) {
            $txt .= $l . "\n";
        }
        return $txt;

    }

    public static function saveLogs($file)
    {
        if (self::getInlineLogs() !== '') {
            try {
                $f = fopen($file, 'a');
                if ($f === false) {
                    throw new \Exception('Canno\'t open file ' . htmlentities($file));
                }
                if (false === fwrite($f, self::getInlineLogs())) {
                    fclose($f);
                    throw new \Exception('Canno\'t write to file ' . htmlentities($file));
                }
                fclose($f);
                self::clearLogs();
            } catch (\Exception $e) {
                syslog(LOG_ERR, $e->getMessage());
                die('An error happened'); //
            }
        }

    }

    public static function defaultSaveLogs()
    {
        self::saveLogs(ROOTDIR . '/log/logger.log');

    }

    public static function clearLogs()
    {
        self::$logs = array();

    }

}