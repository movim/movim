<?php
namespace system\Logs;
if (!defined('DOCUMENT_ROOT')) die('Access denied');

/**
 * Class for managing Logs
 */
abstract class Logger
{

    static $logs;
    static $cssOutputDone=false;
    static $warningDevelopmentModeDisplayed=false;

    
    static function log($message,$level=E_NOTICE,$canal='debug',$file=null,$line=null)
    {
        self::addLog($message,$level,$canal,$file,$line);

    }

    static function addLog($message,$level=E_NOTICE,$canal='debug',$file=null,$line=null)
    {
        if (!isset(self::$logs)) {
            self::$logs = new \system\Logs\Logs();
        }
        self::$logs->addLog($message,$level,$canal,$file,$line);

    }
    static function displayLog()
    {
        if (!isset(self::$logs)) {
            self::$logs = new \system\Logs\Logs();
        }
        self::$logs->displayLog();
    }
    
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
        if (!self::$cssOutputDone) {
            print '
            <style type="text/css">
                body {
                    font-family: sans-serif;
                }

                a:link, a:visited {
                    text-decoration: none;
                    color: #32434D;
                }

                #debug {
                    max-width: 1024px;
                    margin: 0 auto;
                    background-color: white;
                    padding: 5px;
                }

                #debug img {
                    float: right;
                    margin-top: 5px;
                }

                #logs {
                    font-family: monospace;
                    background-color: #353535;
                    color: white;
                    padding: 5px;
                    margin: 5px 0;
                }
                .dev {
                    padding:10px;
                    height: 3em;
                    line-height: 3em;
                    background-color: yellow;
                    display: block;
                    clear:both;
                    /*position: fixed;
                    bottom: 0;
                    left: 50%;
                    text-align: center;*/
                    color: black;

                    /*width: 40em;
                    margin-left: -20em;*/

                    border-radius: 0.1em 0.1em 0 0;

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
                    opacity: 0.7;
                }
            </style>';
            self::$cssOutputDone=true;
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
            <div id="debug">
                 <?php
                  self::displayWarningDevelopmentMessage();
                  
                  if (count(self::getLogs())) {
                    ?>
                    <div id="logs">
                      <?php 
                            echo self::displayLog();
                            self::$logs->defaultSaveLogs();//clear logs
                      ?>
                    </div>
                    <?php
                  }
                  if (class_exists('ControllerBase') && class_exists('Route') ) {
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
        if (!self::$warningDevelopmentModeDisplayed) {
            if (ENVIRONMENT === 'development') {
                if (function_exists('t')) {
                    print '<div class="dev">
                        <p>'.t('Development environment.').'</p>
                        <p>'.t('Change it in the admin panel.').'</p>
                    </div>';
                } else {
                    print '<div class="dev">
                        <p>Be careful you are currently in development environment</p>
                    </div>';
                }
            }
            self::$warningDevelopmentModeDisplayed = true;
        }
    }

}
