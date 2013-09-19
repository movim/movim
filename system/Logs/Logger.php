<?php
namespace system\Logs;
if (!defined('DOCUMENT_ROOT')) die('Access denied');

/**
 * Class for managing Logs
 */
abstract class Logger
{

    /**
     * Singleton collection of logs
     * @var \system\Logs
     */
    static $logs;
    static $cssOutputDone=false;
    /**
     * @var bool Save if warning message is already displayed
     */
    static $warningDevelopmentModeDisplayed=false;

    /***
     * Just an alias of addLog
     */
    static function log($message,$level=E_NOTICE,$canal='debug',$file=null,$line=null)
    {
        return self::addLog($message,$level,$canal,$file,$line);
    }

    /**
     * just an access point to adding a log to the Logs collection
     */
    static function addLog($message,$level=E_NOTICE,$canal='debug',$file=null,$line=null)
    {
        if (!isset(self::$logs)) {
            self::$logs = new \system\Logs\Logs();
        }
        self::$logs->addLog($message,$level,$canal,$file,$line);
    }
    /**
     * just an access point to displayLogs
     */
    static function displayLogs()
    {
        if (!isset(self::$logs)) {
            self::$logs = new \system\Logs\Logs();
        }
        if (DOCTYPE==='text/html') {
            self::$logs->displayLogs();
        } else {
            self::$logs->displayInlineLogs();
        }
    }
    /**
     * don't realy know if it is better to set it protected...
     * @return array
     */
    static function getLogs() 
    {
        if (!isset(self::$logs)) {
            self::$logs = new \system\Logs\Logs();
        }
        return self::$logs->getLogs();
    }
    /**
     * ouput debug css if not already done
     */
    static function displayDebugCSS() 
    {
        if (DOCTYPE==='text/html') {
            if (!self::$cssOutputDone) {
                print '
                <style type="text/css">
                    .carreful h2, 
                    #final_exception h2 {
                        color: red;
                    }

                    .carreful, .debug {
                        margin: 0 auto;
                    }

                    .debug img {
                        float: right;
                        margin-top: 5px;
                    }

                    #logs {
                        font-family: monospace;
                    }
                    .dev {
                        padding: 0.5em;
                        padding-top: 0.2em;
                        background-color: yellow;
                        display: block;
                        clear:both;
                        color: black;

                        background-size: 5em 5em;
                        background-image: linear-gradient(135deg, rgba(0, 0, 0, 0.5) 25%, transparent 25%,
                                            transparent 50%, rgba(0, 0, 0, 0.5) 50%, rgba(0, 0, 0, 0.5) 75%,
                                            transparent 75%, transparent);
                        background-image: -moz-linear-gradient(135deg, rgba(0, 0, 0, 0.5) 25%, transparent 25%,
                                            transparent 50%, rgba(0, 0, 0, 0.5) 50%, rgba(0, 0, 0, 0.5) 75%,
                                            transparent 75%, transparent);
                        background-image: -webkit-linear-gradient(135deg, rgba(0, 0, 0, 0.5) 25%, transparent 25%,
                                            transparent 50%, rgba(0, 0, 0, 0.5) 50%, rgba(0, 0, 0, 0.5) 75%,
                                            transparent 75%, transparent);
                        background-image: -o-linear-gradient(135deg, rgba(0, 0, 0, 0.5) 25%, transparent 25%,
                                            transparent 50%, rgba(0, 0, 0, 0.5) 50%, rgba(0, 0, 0, 0.5) 75%,
                                            transparent 75%, transparent);

                        pointer-events: none;
                    }
                </style>';
                self::$cssOutputDone=true;
            }
        }
    }
    
    /**
     * Warning message and display logs
     */
    static function displayFooterDebug() 
    {
        if (ENVIRONMENT === 'development' && 
                (!self::$warningDevelopmentModeDisplayed ||
                    count(self::getLogs())
                )) {
            \system\Logs\Logger::displayDebugCSS();
            ?>
            <div id="debug" class="debug">
                 <?php
                  self::displayWarningDevelopmentMessage();
                  
                  if (count(self::getLogs())) {
                    ?>
                    <div id="logs">
                      <?php 
                            self::displayLogs();
                            self::$logs->defaultSaveLogs();//clear logs
                      ?>
                    </div>
                    <?php
                  }
                  if (class_exists('ControllerBase') && class_exists('Route') ) {
                      /**
                       * @todo FIX THE CALL TO URLIZE
                       */
                      /*
                        ?>
                        <p>Maybe you can fix some issues with the <a href="<?php echo Route::urlize('admin'); ?>">admin panel</a></p>
                        <?php*/
                  }
                  ?>
            </div>
            <?php 
        }
    }
    /**
     * display warning develoment mode if not already done
     */
    static function displayWarningDevelopmentMessage() 
    {
        if (DOCTYPE==='text/html') {
            if (!self::$warningDevelopmentModeDisplayed) {
                if (ENVIRONMENT === 'development') {
                    if (function_exists('t')) {
                        print '
                        <div class="dev">
                            <p>'.t('Development environment.').' - '.t('Change it in the admin panel.').'</p>
                        </div>';
                    } else {
                        print '
                        <div class="dev">
                            <p>Be careful you are currently in development environment</p>
                        </div>';
                    }
                }
                self::$warningDevelopmentModeDisplayed = true;
            }
        }
    }

}
