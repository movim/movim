<?php

/* WTF ? This number will not give me any clue
 * if (!defined('DOCUMENT_ROOT')) {
    die('Error 57895');
}
if (!is_dir(DOCUMENT_ROOT)) {
    die('Error 57896');
}*/

/**
 * \brief Movim's logger class.
 *
 * Static class to be invoked for every debug log purpose in Movim.
 */
/*class Logs
{

    static $defined;

    protected $logs = array();
    public function __construct()
    {
        if (self::$defined === true) {
            die('standalone');
        }
        self::$defined = true;

    }

    

    public function log($message)
    {
        $this->addLog($message);

    }

    public function addLog($message)
    {
        if (!is_string($message)) {
            $message = var_export($message, true);
        }
        array_push($this->logs, '['.date('r').'] '.$message);

    }
*/
    /**
     * getter logs
     * @return array
     */
/*
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
        print $html;

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
                
                //var_export($e);
                if (ENVIRONMENT === 'development') {
                    die(\system\Debug::getDump($e, 3, true));
                } 
                syslog(LOG_ERR, $e->getMessage());
                die('An error happened'); //
                
            }
        }

    }

    public function defaultSaveLogs()
    {
        $this->saveLogs(DOCUMENT_ROOT . '/log/logger.log');

    }

    public function clearLogs()
    {
        $this->logs = array();

    }
    
    static function errorLevel($intval)
    {
        $errorLevels = array(
            2047 => 'E_ALL',
            1024 => 'E_USER_NOTICE',
            512 => 'E_USER_WARNING',
            256 => 'E_USER_ERROR',
            128 => 'E_COMPILE_WARNING',
            64 => 'E_COMPILE_ERROR',
            32 => 'E_CORE_WARNING',
            16 => 'E_CORE_ERROR',
            8 => 'E_NOTICE',
            4 => 'E_PARSE',
            2 => 'E_WARNING',
            1 => 'E_ERROR');
        $result = '';
        foreach($errorLevels as $number => $name)
        {
            if (($intval & $number) == $number) {
                $result .= ($result != '' ? '&' : '').$name; }
        }
        return $result;
    }

    function __destruct()
    {
        $this->defaultSaveLogs();

    }

}
function systemErrorHandler ( $errno , $errstr , $errfile ,  $errline , $errcontext=null ) 
{
    Logger::addLog('['.Logs::errorLevel($errno).'] '.$errstr."\n".var_export(array('errfile'=>$errfile,'errline'=>$errline),true));
    return false;
}*/
abstract class Logger
{

    static $logs = array();

    /*
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
    static function displayLog()
    {
        if (!isset(self::$logs)) {
            self::$logs = new Logs();
        }
        self::$logs->displayLog();


    }
    */
    
    static function log($message) {
        array_push(self::$logs, $message);

        openlog('modl', LOG_NDELAY, LOG_USER);
        $errlines = explode("\n",$message);
        foreach ($errlines as $txt) { syslog(LOG_DEBUG, trim($txt)); } 
        closelog();
    }
    
    static function displayLog() {
        foreach(self::$logs as $l) {
            echo $l.'<br />';
        }
    }
    
    static function getLog() {
        if(count(self::$logs) == 0)
            return null;
        else
            return self::$logs;
    }
}
