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
class Logs
{

    static $defined;

    public function __construct()
    {
        if (self::$defined === true) {
            die('standalone');
        }
        self::$defined = true;

    }

    private $logs = array();

    public function log($message)
    {
        $this->addLog($message);

    }

    public function addLog($message)
    {
        if (!is_string($message)) {
            $message = var_export($message, true);
        }
        array_push($this->logs, $message);

    }

    /**
     * getter logs
     * @return array
     */
    public function getLog()
    {
        return $this->logs;

    }

    public function displayLog()
    {
        $logs = $this->getLog();
        $html = '';
        if (!empty($logs)) {
            $html = '<div class="message error">';
            foreach ($logs as $l) $html .= $l . '<br />';
            $html .= '</div>';
        }
        return $html;

    }

    public function getInlineLogs()
    {
        $logs = $this->getLog();
        $txt = '';
        foreach ($logs as $l) {
            if (trim($l)) {
                $txt .= $l . "\n";
            }
        }
        return $txt;

    }

    public function saveLogs($file)
    {
        if ($this->getInlineLogs() !== '') {
            try {
                $f = fopen($file, 'a');
                if ($f === false) {
                    throw new \Exception('Canno\'t open file ' . htmlentities($file));
                }
                if (false === fwrite($f, $this->getInlineLogs())) {
                    fclose($f);
                    throw new \Exception('Canno\'t write to file ' . htmlentities($file));
                }
                fclose($f);
                $this->clearLogs();
            } catch (\Exception $e) {
                syslog(LOG_ERR, $e->getMessage());
                die('An error happened'); //
            }
        }

    }

    public function defaultSaveLogs()
    {
        $this->saveLogs(ROOTDIR . '/log/logger.log');

    }

    public function clearLogs()
    {
        $this->logs = array();

    }

    function __destruct()
    {
        $this->defaultSaveLogs();

    }

}

abstract class Logger
{

    static $logs;

    static function log($msg)
    {
        self::addLog($msg);

    }

    static function addLog($msg)
    {
        if (!isset(self::$logs)) {
            self::$logs = new Logs();
        }
        self::$logs->addLog($msg);

    }

}