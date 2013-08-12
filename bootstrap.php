<?php

class Bootstrap {
    function boot() {
        mb_internal_encoding("UTF-8");
        
        $this->setContants();
        $this->setBrowserSupport();
        $this->loadSystem();
        $this->loadCommonLibraries();
        $this->loadDispatcher();
        
        $this->setTimezone();
        $this->setLogs();
        
        $loadmodlsuccess = $this->loadModl();
        $this->loadMoxl();
        
        if($loadmodlsuccess) {
            $this->startingSession();
            
            if(Logger::getLog() != null) {
                return false;
            }
            
            return true;
        } else {
            $this->bootLogs();
            return false;
        }
    }
    
    private function setContants() {
        define('APP_TITLE',     'Movim');
        define('APP_NAME',      'movim');
        define('APP_VERSION',   $this->getVersion());
        define('BASE_URI',      $this->getBaseUri());
        
        define('THEMES_PATH',   DOCUMENT_ROOT . '/themes/');
        define('USERS_PATH',    DOCUMENT_ROOT . '/users/');
        define('APP_PATH',      DOCUMENT_ROOT . '/app/');
        define('SYSTEM_PATH',   DOCUMENT_ROOT . '/system/');
        define('LIB_PATH',      DOCUMENT_ROOT . '/lib/');
        define('LOCALES_PATH',  DOCUMENT_ROOT . '/locales/');
        define('CACHE_PATH',    DOCUMENT_ROOT . '/cache/');
    }

    private function getVersion() {
        $file = "VERSION";
        if($f = fopen(DOCUMENT_ROOT.'/'.$file, 'r')) {
            return trim(fgets($f));
        }
    }
    
    private function getBaseUri() {        
        $path = dirname($_SERVER['PHP_SELF']).'/';
        // Determining the protocol to use.
        $uri = "http://";
        if((
            isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != "") 
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == "https")) {
            $uri = 'https://';
        }

        if($path == "") {
            $uri .= $_SERVER['HTTP_HOST'] . '/';
        } else {
            $uri .= $_SERVER['HTTP_HOST'] . $path;
        }

        $uri = str_replace('jajax.php', '', $uri);
        
        return $uri;
    }
    
    private function loadSystem() {
        // Loads up all system libraries.
        require_once(SYSTEM_PATH . "/i18n/i18n.php");

        require_once(SYSTEM_PATH . "Session.php");
        require_once(SYSTEM_PATH . "Utils.php");
        require_once(SYSTEM_PATH . "UtilsPicture.php");
        require_once(SYSTEM_PATH . "Cache.php");
        require_once(SYSTEM_PATH . "Conf.php");
        require_once(SYSTEM_PATH . "Event.php");
        require_once(SYSTEM_PATH . "Logger.php");
        require_once(SYSTEM_PATH . "MovimException.php");
        require_once(SYSTEM_PATH . "RPC.php");
        require_once(SYSTEM_PATH . "User.php");
    }
    
    private function loadCommonLibraries() {
        // XMPPtoForm lib
        require_once(LIB_PATH . "XMPPtoForm.php");

        // Markdown lib
        require_once(LIB_PATH . "Markdown.php");
        
        // The template lib
        require_once(LIB_PATH . 'RainTPL.php');
    }
    
    private function loadDispatcher() {
        require_once(APP_PATH . "controllers/ControllerBase.php");
        require_once(APP_PATH . "controllers/ControllerMain.php");
        require_once(APP_PATH . "controllers/ControllerAjax.php");

        require_once(SYSTEM_PATH . "Route.php");

        require_once(SYSTEM_PATH . "Tpl/TplPageBuilder.php");

        require_once(SYSTEM_PATH . "widget/WidgetBase.php");
        require_once(SYSTEM_PATH . "widget/WidgetWrapper.php");

        require_once(APP_PATH . "widgets/WidgetCommon/WidgetCommon.php");
        require_once(APP_PATH . "widgets/Notification/Notification.php");
    }
    
    private function setLogs() {
        try {
            define('ENVIRONMENT',Conf::getServerConfElement('environment'));
        } catch (Exception $e) {
            define('ENVIRONMENT','development');//default environment is production
        }
        if (ENVIRONMENT === 'development') {
            ini_set('log_errors', 1);
            ini_set('display_errors', 0);
            ini_set('error_reporting', E_ALL );
            
        } else {
            ini_set('log_errors', 1);
            ini_set('display_errors', 0);
            ini_set('error_reporting', E_ALL ^ E_DEPRECATED ^ E_NOTICE);
        }
        ini_set('error_log', DOCUMENT_ROOT.'/log/php.log');
    }
    
    private function setTimezone() {
        // We set the default timezone to the server timezone
        $conf = Conf::getServerConf();
        if(isset($conf['timezone']))
            date_default_timezone_set($conf['timezone']);
    }
    
    private function loadModl() {
        // We load Movim Data Layer
        require_once(LIB_PATH . 'Modl/loader.php');
        
        $db = modl\Modl::getInstance();
        $db->setConnectionArray(Conf::getServerConf());
        
        return $db->testConnection();
    }
    
    private function loadMoxl() {
        // We load Movim XMPP Library
        require_once(LIB_PATH . 'Moxl/loader.php');
    }
    
    private function setBrowserSupport() {
        if(isset( $_SERVER['HTTP_USER_AGENT'])) {
            $useragent = $_SERVER['HTTP_USER_AGENT'];

            if (preg_match('|MSIE ([0-9].[0-9]{1,2})|',$useragent,$matched)) {
                $browser_version=$matched[1];
                $browser = 'IE';
            } elseif (preg_match('/Opera[\/ ]([0-9]{1}\.[0-9]{1}([0-9])?)/',$useragent,$matched)) {
                $browser_version=$matched[1];
                $browser = 'Opera';
            } elseif(preg_match('|Firefox/([0-9\.]+)|',$useragent,$matched)) {
                $browser_version=$matched[1];
                $browser = 'Firefox';
            } elseif(preg_match('|Safari/([0-9\.]+)|',$useragent,$matched)) {
                $browser_version=$matched[1];
                $browser = 'Safari';
            }
        } else {
            $browser_version = 0;
            $browser= 'other';
        }

        define('BROWSER_VERSION', $browser_version);
        define('BROWSER', $browser);

        $compatible = false;

        switch($browser) {
            case 'Firefox':
                if($browser_version > 3.5)
                    $compatible = true;
            break;
            case 'IE':
                if($browser_version > 9.0)
                    $compatible = true;
            break;
            case 'Safari': // Also Chrome-Chromium
                if($browser_version > 522.0)
                    $compatible = true;
            break;
            case 'Opera':
                if($browser_version > 9.0)
                    $compatible = true;
            break;
        }

        define('BROWSER_COMP', $compatible);
    }
    
    private function startingSession() {
        global $session;
        // Starting session.
        $sess = Session::start(APP_NAME);
        $session = $sess->get('session');
    }
    
    /*
     * Display the boot errors
     */
    public function bootLogs() {
        ?>
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
                padding: 2em;
            }
            
            #debug img {
                float: right;
                margin-top: 2em;
            }
            
            #logs {
                font-family: monospace;
                background-color: #353535;
                color: white;
                padding: 1em;
                margin: 1em 0;
            }
        </style>

        <div id="debug">
            <?php         
            if (ENVIRONMENT === 'development') {
            ?>
                <div class="carreful">
                    <p>Be careful you are currently in development environment</p>
                </div>
                <div id="logs">
                  <?php echo Logger::displayLog(); ?>
                </div>
                
                Maybe you can fix some issues with the <a href="<?php echo Route::urlize('admin'); ?>">admin panel</a>            
            <?php 
            } elseif (ENVIRONMENT === 'production') {
                ?>
                <div class="carreful">
                    <p>Oops... something went wrong.<br />But don't panic. The NSA is on the case.</p>
                </div>
                <?php
            }   
            ?>      
            <a href="http://movim.eu/">
                <img src="<?php echo BASE_URI.'themes/movim/img/logo_black.png'; ?>" />
            </a>
            <div class="clear"></div>
        </div>  
    <?php
    }
}
