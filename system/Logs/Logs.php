<?php
namespace system\Logs;
if (!defined('DOCUMENT_ROOT')) die('Access denied');
/**
 * this class is a collection for managing logs (in array).
 * It's a singleton, on destruct, save all logs
 */
class Logs
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

    

    public function log($message,$level=E_NOTICE,$canal='debug',$file=null,$line=null)
    {
        $this->addLog($message,$level,$canal,$file,$line);

    }

    public function addLog($message,$level=E_NOTICE,$canal='debug',$file=null,$line=null)
    {
        if (!is_string($message)) {
            $message = var_export($message, true);
        }
        array_push($this->logs, array('message'=>$message,'level'=>$level,'file'=>$file,'line'=>$line,'canal'=>$canal,'date'=> time()));
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
     * 
     * @param array $log
     */
    protected function getDisplayLog($log,$stripTags=false,$removeBreak=false)
    {
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
    public function displayLog()
    {
        $logs = $this->getLogs();
        $html = '';
        if (!empty($logs)) {
            $html = '<div class="message error">';
            foreach ($logs as $l) $html .= $this->getDisplayLog($l). '<br />';
            $html .= '</div>';
        }
        print $html;

    }

    public function getInlineLogs()
    {
        $logs = $this->getLogs();
        $txt = '';
        foreach ($logs as $l) {
            
                $txt .= $this->getDisplayLog($l,true,true) . "\n";
            
        }
        return $txt;

    }

    public function saveLogs($file)
    {
        if (count($this->logs)) {
            try {
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
                    foreach ($this->logs as $log) {
                        error_log($this->getDisplayLog($log,true,true));
                    }
                    $this->clearLogs();
                } else if (LOG_MANAGEMENT == 'syslog') {
                    foreach ($this->logs as $log) {
                        syslog($log['level'], $this->getDisplayLog($log,true,true));
                    }
                    $this->clearLogs();
                } else {
                    throw new \Exception('Error configuration: LOG_MANAGEMENT not defined');
                }
            } catch (\Exception $e) {
                if (ENVIRONMENT === 'development') {
                    die(\system\Debug::getDump($e, 3, true));
                } 
                syslog(LOG_ERR, $e->getMessage());
                die('An error happened'); 
                
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
        if ($result =='') {
            return 'E_LVL_'.$intval;
        }
        return $result;
    }

    function __destruct()
    {
        $this->defaultSaveLogs();

    }

}
?>