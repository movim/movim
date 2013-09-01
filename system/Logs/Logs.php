<?php
namespace system\Logs;
if (!defined('DOCUMENT_ROOT')) die('Access denied');
/**
 * this class is a collection for managing logs (in array).
 * It's a singleton, on destruct, save all logs
 */
class Logs
{

    const LEVEL_TO_STOP = E_ERROR;
    /**
     * set if a logs is already defined: Singleton, only one "new Logs();"
     * @var bool
     */
    static protected $defined;

    protected $logs = array();
    public function __construct()
    {
        if (self::$defined === true) {
            die('standalone');
        }
        self::$defined = true;

    }
    
    /**
     * When script die, save all logs not already saved
     */
    public function __destruct()
    {
        $this->defaultSaveLogs();
    }

    /**
     * alias function of addLog (shorter)
     */
    public function log($message,$level=E_NOTICE,$canal='debug',$file=null,$line=null)
    {
        $this->addLog($message,$level,$canal,$file,$line);

    }

    /**
     * adding log array to collection. It's the only access point for this... !!! 
     * Trow an exception if error level is too critical
     * @param sting $message
     * @param int $level Define your log level criticity
     * @param string $canal tag your log, debug, system, error... 
     * @param string $file
     * @param int $line
     */
    public function addLog($message,$level=E_NOTICE,$canal='debug',$file=null,$line=null)
    {
        $level = (int)$level;
        $line = (int)$line;
        if (!is_string($message)) {
            $message = var_export($message, true);
        }
        $log = array('message'=>$message,'level'=>$level,'file'=>$file,'line'=>$line,'canal'=>$canal,'date'=> time());
        array_push($this->logs, $log);
        $this->writeSingleLog($log);
        if ($level <= self::LEVEL_TO_STOP) {
            $this->criticalEvent($log);
        }
        
    }
    /**
     * just defined what to do when a critical log is sent here
     * @param array $log
     * @throws Exception
     */
    protected function criticalEvent($log) {
        $file ='';
        $line=0;
        if (!headers_sent ($file , $line )) {
            if (ENVIRONMENT === 'development') {
                header('HTTP/1.1 500 '.$log['message']);
            } else {
                header('HTTP/1.1 500 Internal Server Error');
            }
            
        } else {
            $log['message'].= ' - Headers sent on file '.$file.' l.'.$line;
        }
        if (ENVIRONMENT === 'development') {
            
            if (DOCTYPE!=='text/html') {
                $this->displayInlineLogs();
            }
            ob_end_flush();
            throw new \Exception('Fatal Error : '.$this->getDisplayLog($log,true, true),$log['level']);
        } else {
            ob_end_flush();
            throw new \Exception('Fatal Error');
        }
        
    }

    /**
     * getter logs
     * @return array
     */
    public function getLogs()
    {
        return $this->logs;

    }

    /**
     * get One log display, in html or in text
     * @param array $log
     * @param bool $stripTags
     * @param type $removeBreak
     * @return string
     */
    protected function getDisplayLog($log,$stripTags=false,$removeBreak=false)
    {
        if (!is_array($log)) {
            throw new Exception('Error type in getDisplayLog:"'.  gettype($log).'"');
        }
        if ($stripTags) {
            $log['message'] = strip_tags($log['message']);
        }
        if ($removeBreak) {
            $log['message'] = str_replace("\n", ' ', $log['message']);
        }
        $strReturn = '['.date('r',$log['date']).'] ['.$log['canal'].'] ['.self::errorLevel($log['level']).'] '.$log['message'];
        if (!is_null($log['file'])) {
            $strReturn .= ' on '.$log['file'];
        }
        if (!is_null($log['line'])) {
            $strReturn .= ' l.'.$log['line'];
        }
        return $strReturn;
    }
    /**
     * Function to display all logs in html
     * @return string
     */
    public function displayLogs()
    {
        $logs = $this->getLogs();
        $html = '';
        if (!empty($logs)) {
            $html = '<div class="message logs">';
            foreach ($logs as $l) $html .= $this->getDisplayLog($l). '<br />';
            $html .= '</div>';
        }
        print $html;

    }
    /**
     * Function to display all logs inline
     * @return string
     */
    public function displayInlineLogs()
    {
        $logs = $this->getLogs();
        $text = '';
        if (!empty($logs)) {
            foreach ($logs as $l) $text .= $this->getDisplayLog($l,true,true). "\n";
        }
        print $text;

    }

    /**
     * Function to return all logs in text
     * @param bool $stripTags
     * @return string
     */
    public function getInlineLogs($removeBreak=true)
    {
        $logs = $this->getLogs();
        $txt = '';
        foreach ($logs as $l) {
            
                $txt .= $this->getDisplayLog($l,true,$removeBreak) . "\n";
            
        }
        return $txt;

    }

    /**
     * save and clear logs
     * @param string $file
     */
    public function saveAndClearLogs($file)
    {
        if (count($this->logs)) {
            try {
                $this->saveLogs($file);
            } catch (\Exception $e) {
                if (ENVIRONMENT === 'development') {
                    die(\system\Debug::getDump($e, 3, true));
                } 
                syslog(LOG_ERR, $e->getMessage());
                die('An error happened'); 
                
            }
        }
    }
    /**
     * Save logs to a file 
     * @param string $file
     * @throws \Exception
     */
    protected function saveLogs($file)
    {
        if (LOG_MANAGEMENT == 'log_folder') {
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
        } else if (LOG_MANAGEMENT == 'error_log') {
            /*foreach ($this->logs as $log) {
                error_log($this->getDisplayLog($log,true,true));
            }*/
            $this->clearLogs();
        } else if (LOG_MANAGEMENT == 'syslog') {
            /*foreach ($this->logs as $log) {
                syslog($log['level'], $this->getDisplayLog($log,true,true));
            }*/
            $this->clearLogs();
        } else {
            throw new \Exception('Error configuration: LOG_MANAGEMENT not defined');
        }

    }

    /**
     * by default, save to logger.log file
     */
    public function defaultSaveLogs()
    {
        $this->saveAndClearLogs(DOCUMENT_ROOT . '/log/logger.log');
    }

    /**
     * clear logs collection
     */
    protected function clearLogs()
    {
        $this->logs = array();

    }
    /**
     * if log is done by system, don't wait end of execution to write into file
     * @param array $log
     */
    protected function writeSingleLog($log)
    {
        if (LOG_MANAGEMENT == 'error_log') {
            error_log($this->getDisplayLog($log,true,true));
            
        } else if (LOG_MANAGEMENT == 'syslog') {
            if (!syslog($log['level'], $this->getDisplayLog($log,true,true))) {
                throw new \Exception('Error, unable to use syslog');
            }
         
        }
    }

    
    /**
     * @param int $intval
     * @return string
     */
    static function errorLevel($intval)
    {
        $intval = (int)$intval;
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
        if ($result =='') {
            return 'E_LVL_'.$intval;
        }
        return $result;
    }
}
