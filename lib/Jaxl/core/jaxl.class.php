<?php
/** 
 * Jaxl (Jabber XMPP Library)
 *
 * Copyright (c) 2009-2010, Abhinav Singh <me@abhinavsingh.com>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Abhinav Singh nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRIC
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 * 
 * @package jaxl
 * @subpackage core
 * @author Abhinav Singh <me@abhinavsingh.com>
 * @copyright Abhinav Singh
 * @link http://code.google.com/p/jaxl
 */

    declare(ticks=1);
    
    // Set JAXL_BASE_PATH if not already defined by application code
    if(!@constant('JAXL_BASE_PATH'))
        define('JAXL_BASE_PATH', dirname(dirname(__FILE__)));

    /**
     * Autoload method for Jaxl library and it's applications
     *
     * @param string|array $classNames core class name required in PHP environment
     * @param object $jaxl Jaxl object which require these classes. Optional but always required while including implemented XEP's in PHP environment
    */
    function jaxl_require($classNames, $jaxl=false) {
        static $included = array();
        $tagMap = array(
            // core classes
            'JAXLBosh' => '/core/jaxl.bosh.php',
            'JAXLCron' => '/core/jaxl.cron.php',
            'JAXLHTTPd' => '/core/jaxl.httpd.php',
            'JAXLog' => '/core/jaxl.logger.php',
            'JAXLXml' => '/core/jaxl.parser.php',
            'JAXLPlugin' => '/core/jaxl.plugin.php',
            'JAXLUtil' => '/core/jaxl.util.php',
            'JAXLS5B' => '/core/jaxl.s5b.php',
            'XML' => '/core/jaxl.xml.php',  
            // xmpp classes
            'XMPP' => '/xmpp/xmpp.class.php',
            'XMPPGet' => '/xmpp/xmpp.get.php',
            'XMPPSend' => '/xmpp/xmpp.send.php',
            'XMPPAuth' => '/xmpp/xmpp.auth.php'
        );
        
        if(!is_array($classNames)) $classNames = array('0'=>$classNames);
        foreach($classNames as $key => $className) {
            $xep = substr($className, 4, 4);
            if(substr($className, 0, 4) == 'JAXL'
            && is_numeric($xep)
            ) { // is XEP
                if(!isset($included[$className])) {
                    require_once JAXL_BASE_PATH.'/xep/jaxl.'.$xep.'.php';
                    $included[$className] = true;
                }
                call_user_func(array('JAXL'.$xep, 'init'), $jaxl);
            } // is Core file
            else if(isset($tagMap[$className])) {
                require_once JAXL_BASE_PATH.$tagMap[$className];
                $included[$className] = true;
            }
        }
        return;
    }

    // Include core classes and xmpp base
    jaxl_require(array(
        'JAXLog',
        'JAXLUtil',
        'JAXLPlugin',
        'JAXLCron',
        'XML',
        'XMPP',
    ));

    /**
     * Jaxl class extending base XMPP class
     *
     * Jaxl library core is like any of your desktop Instant Messaging (IM) clients.
     * Include Jaxl core in you application and start connecting and managing multiple XMPP accounts
     * Packaged library is custom configured for running <b>single instance</b> Jaxl applications
     * 
     * For connecting <b>multiple instance</b> XMPP accounts inside your application rewrite Jaxl controller
     * using combination of env/jaxl.php, env/jaxl.ini and env/jaxl.conf
    */
    class JAXL extends XMPP {

        /**
         * Client version of the connected Jaxl instance
        */
        const version = '2.1.2';
        
        /**
         * Client name of the connected Jaxl instance
        */
        const name = 'Jaxl :: Jabber XMPP Client Library';

        /**
         * Custom config passed to Jaxl constructor
         * 
         * @var array
        */
        var $config = array();

        /**
         * Username of connecting Jaxl instance
         *
         * @var string
        */
        var $user = false;

        /**
         * Password of connecting Jaxl instance
         *
         * @var string
        */
        var $pass = false;

        /**
         * Hostname for the connecting Jaxl instance
         * 
         * @var string
        */
        var $host = 'localhost';

        /**
         * Port for the connecting Jaxl instance
         * 
         * @var integer
        */
        var $port = 5222;

        /**
         * Full JID of the connected Jaxl instance
         *
         * @var string
        */
        var $jid = false;

        /**
         * Domain for the connecting Jaxl instance
         * 
         * @var string
        */
        var $domain = 'localhost';

        /**
         * Resource for the connecting Jaxl instance
         *
         * @var string
        */
        var $resource = false;

        /**
         * Log Level of the connected Jaxl instance
         * 
         * @var integer
        */
        var $logLevel = 1;

        /**
         * Enable/Disable automatic log rotation for this Jaxl instance
         *
         * @var bool|integer
        */
        var $logRotate = false;

        /**
         * Absolute path of log file for this Jaxl instance
        */
        var $logPath = '/var/log/jaxl.log';

        /**
         * Absolute path of pid file for this Jaxl instance
        */
        var $pidPath = '/var/run/jaxl.pid';

        /**
         * Enable/Disable shutdown callback on SIGH terms
         *
         * @var bool
        */
        var $sigh = true;

        /**
         * Process Id of the connected Jaxl instance
         *
         * @var bool|integer
        */
        var $pid = false;

        /**
         * Mode of the connected Jaxl instance (cgi or cli)
         *
         * @var bool|string
        */
        var $mode = false;

        /**
         * Jabber auth mechanism performed by this Jaxl instance
         *
         * @var false|string
        */
        var $authType = false;

        /**
         * Jaxl instance dumps usage statistics periodically. Disabled if set to "false"
         * 
         * @var bool|integer
        */
        var $dumpStat = 300;

        /**
         * List of XMPP feature supported by this Jaxl instance
         * 
         * @var array
        */
        var $features = array();

        /**
         * XMPP entity category for the connected Jaxl instance
         *
         * @var string
        */
        var $category = 'client';

        /**
         * XMPP entity type for the connected Jaxl instance
         * 
         * @var string
        */
        var $type = 'bot';

        /**
         * Default language of the connected Jaxl instance
        */
        var $lang = 'en';

        /**
         * Location to system temporary folder
        */
        var $tmp = null;

        /**
         * Jaxl core constructor
         *
         * Jaxl instance configures itself using the constants inside your application jaxl.ini.
         * However, passed array of configuration options overrides the value inside jaxl.ini.
         * If no configuration are found or passed for a particular value,
         * Jaxl falls back to default config options.
         * 
         * @param $config Array of configuration options
         * @todo Use DNS SRV lookup to set $jaxl->host from provided domain info
        */
        function __construct($config=array()) {
            $this->mode = (PHP_SAPI == "cli") ? PHP_SAPI : "cgi";
            $this->pid = getmypid();
            $this->config = $config;
            $this->tmp = sys_get_temp_dir();

            /* Mandatory params to be supplied either by jaxl.ini constants or constructor $config array */
            $this->user = isset($config['user']) ? $config['user'] : JAXL_USER_NAME;
            $this->pass = isset($config['pass']) ? $config['pass'] : JAXL_USER_PASS;
            $this->domain = isset($config['domain']) ? $config['domain'] : (@constant("JAXL_HOST_DOMAIN") == null ? $this->domain : JAXL_HOST_DOMAIN);
            
            /* Optional params if not configured using jaxl.ini or $config take default values */
            $this->port = isset($config['port']) ? $config['port'] : (@constant("JAXL_HOST_PORT") == null ? $this->port : JAXL_HOST_PORT);
            $this->host = isset($config['host']) ? $config['host'] : (@constant("JAXL_HOST_NAME") == null ? $this->domain : JAXL_HOST_NAME);
            $this->resource = isset($config['resource']) ? $config['resource'] : (@constant("JAXL_USER_RESC") == null ? "jaxl.".time() : JAXL_USER_RESC);
            $this->logLevel = isset($config['logLevel']) ? $config['logLevel'] : (@constant("JAXL_LOG_LEVEL") == null ? $this->logLevel : JAXL_LOG_LEVEL);
            $this->logRotate = isset($config['logRotate']) ? $config['logRotate'] : (@constant("JAXL_LOG_ROTATE") == null ? $this->logRotate : JAXL_LOG_ROTATE);
            $this->logPath = isset($config['logPath']) ? $config['logPath'] : (@constant("JAXL_LOG_PATH") == null ? $this->logPath : JAXL_LOG_PATH);
            $this->pidPath = isset($config['pidPath']) ? $config['pidPath'] : (@constant("JAXL_PID_PATH") == null ? $this->pidPath : JAXL_PID_PATH);
            
            /* Handle pre-choosen auth type mechanism */
            $this->authType = isset($config['authType']) ? $config['authType'] : (@constant("JAXL_AUTH_TYPE") == null ? $this->authType : JAXL_AUTH_TYPE);
            if($this->authType) 
                JAXLPlugin::add('jaxl_get_auth_mech', array($this, 'doAuth'));

            /* Optional params which can be configured only via constructor $config */
            $this->sigh = isset($config['sigh']) ? $config['sigh'] : true;
            $this->dumpStat = isset($config['dumpStat']) ? $config['dumpStat'] : 300;

            /* Configure instance for platforms and call parent construct */
            $this->configure($config);
            parent::__construct($config);
            $this->xml = new XML();
            
            /* Initialize JAXLCron and register instance cron jobs */
            JAXLCron::init();
            if($this->dumpStat) JAXLCron::add(array($this, 'dumpStat'), $this->dumpStat);
            if($this->logRotate) JAXLCron::add(array('JAXLog', 'logRotate'), $this->logRotate);
            
            // include service discovery XEP-0030, recommended for every XMPP entity
            $this->requires('JAXL0030');
        }
        
        /**
         * Configures Jaxl instance to run across various platforms (*nix/windows)
         *
         * Configure method tunes connecting Jaxl instance for
         * OS compatibility, SSL support and dependencies over PHP methods
        */
        protected function configure() {
            // register shutdown function
            if(!JAXLUtil::isWin() && JAXLUtil::pcntlEnabled() && $this->sigh) {
                pcntl_signal(SIGTERM, array($this, "shutdown"));
                pcntl_signal(SIGINT, array($this, "shutdown"));
                $this->log("Registering callbacks on SIGH terms.");
            }
            else {
                $this->log("No callbacks registered on SIGH terms.");
            }
           
            // check Jaxl dependency on PHP extension in cli mode
            if($this->mode == "cli") {
                if(JAXLUtil::sslEnabled()) 
                    $this->log("OpenSSL extension is loaded.");
                else
                    $this->log("OpenSSL extension not loaded.");
               
                if(!function_exists('fsockopen'))
                    die("Jaxl requires fsockopen method");
                
                if(@is_writable($this->pidPath))
                    file_put_contents($this->pidPath, $this->pid);
            }
            
            // check Jaxl dependency on PHP extension in cgi mode
            if($this->mode == "cgi") {
                if(!function_exists('curl_init'))
                    die("Jaxl requires CURL PHP extension");

                if(!function_exists('json_encode'))
                    die("Jaxl requires JSON PHP extension.");
            }
        }
       
        /**
         * Dumps Jaxl instance usage statistics
         *
         * Jaxl instance periodically calls this methods every JAXL::$dumpStat seconds.
        */
        function dumpStat() {
            $this->log("Memory usage: ".round(memory_get_usage()/pow(1024,2), 2)." Mb, peak: ".round(memory_get_peak_usage()/pow(1024,2), 2)." Mb", 0);
        }
        
        /**
         * Magic method for calling XEP's included by the JAXL instance
         *
         * Application <b>should never</b> call an XEP method directly using <code>JAXL0045::joinRoom()</code>
         * instead use <code>$jaxl->JAXL0045('joinRoom', $param1, ..)</code> to call methods provided by included XEP's
         *
         * @param string $xep XMPP extension (XEP) class name e.g. JAXL0045 for XEP-0045 a.k.a. Multi-User chat extension
         * @param array $param Array of parameters where $param[0]='methodName' is the method called inside JAXL0045 class
         *
         * @return mixed $return Return value of called XEP method
        */
        function __call($xep, $param) {
            $method = array_shift($param);
            array_unshift($param, $this);
            if(substr($xep, 0, 4) == 'JAXL') {
                $xep = substr($xep, 4, 4);
                if(is_numeric($xep)
                && class_exists('JAXL'.$xep)
                ) { return call_user_func_array(array('JAXL'.$xep, $method), $param); }
                else { $this->log("[[JAXL".$xep."]] Doesn't exists in the environment"); }
            }
        } 
      
        /**
         * @return string $name Returns name of this Jaxl client
        */
        function getName() {
            return JAXL::name;
        }

        /**
         * @return float $version Returns version of this Jaxl client
        */
        function getVersion() {
            return JAXL::version;
        }

        /**
         * Shutdown Jaxl instance cleanly
         *
         * shutdown method is auto invoked when Jaxl instance receives a SIGH term.
         * Before cleanly shutting down this method callbacks registered using <b>jaxl_pre_shutdown</b> hook.
         *
         * @param mixed $signal This is passed as paramater to callbacks registered using <b>jaxl_pre_shutdown</b> hook
        */
        function shutdown($signal=false) {
            $this->log("Jaxl Shutting down ...", 1);
            JAXLPlugin::execute('jaxl_pre_shutdown', $signal, $this);
            
            if($this->stream) $this->endStream();
            $this->stream = false;
        }
        
        /**
         * Perform authentication for connecting Jaxl instance
         *
         * @param string $type Authentication mechanism to proceed with. Supported auth types are:
         *               - DIGEST-MD5
         *               - PLAIN
         *               - X-FACEBOOK-PLATFORM
         *               - ANONYMOUS
        */
        function auth($type) {
            $this->authType = $type;
            return XMPPSend::startAuth($this);
        }
       
        /**
         * Perform pre-choosen auth type for the Jaxl instance
        */
        function doAuth($mechanism) {
            return XMPPSend::startAuth($this);
        }
        
        /**
         * Set status of the connected Jaxl instance
         *
         * @param bool|string $status
         * @param bool|string $show
         * @param bool|integer $priority
         * @param bool $caps
        */
        function setStatus($status=false, $show=false, $priority=false, $caps=false, $vcard=false) {
            $child = array();
            $child['status'] = ($status === false ? 'Online using Jaxl library http://code.google.com/p/jaxl' : $status);
            $child['show'] = ($show === false ? 'chat' : $show);
            $child['priority'] = ($priority === false ? 1 : $priority);

            if($caps) $child['payload'] = $this->JAXL0115('getCaps', $this->features);
            if($vcard) $child['payload'] .= $this->JAXL0153('getUpdateData', false);
            
            return XMPPSend::presence($this, false, false, $child, false);
        }
       
        /**
         * Send authorization request to $toJid
         *
         * @param string $toJid JID whom Jaxl instance wants to send authorization request
        */
        function subscribe($toJid) {
            return XMPPSend::presence($this, $toJid, false, false, 'subscribe');
        }
        
        /**
         * Accept authorization request from $toJid
         *
         * @param string $toJid JID who's authorization request Jaxl instance wants to accept
        */
        function subscribed($toJid) {
            return XMPPSend::presence($this, $toJid, false, false, 'subscribed');
        }
        
        /**
         * Send cancel authorization request to $toJid
         *
         * @param string $toJid JID whom Jaxl instance wants to send cancel authorization request
        */
        function unsubscribe($toJid) {
            return XMPPSend::presence($this, $toJid, false, false, 'unsubscribe');
        }
        
        /**
         * Accept cancel authorization request from $toJid
         * 
         * @param string $toJid JID who's cancel authorization request Jaxl instance wants to accept
        */
        function unsubscribed($toJid) {
            return XMPPSend::presence($this, $toJid, false, false, 'unsubscribed');
        }
       
        /**
         * Retrieve connected Jaxl instance roster list from the server
         *
         * @param mixed $callback Method to be callback'd when roster list is received from the server
        */
        function getRosterList($callback=false) {
            $payload = '<query xmlns="jabber:iq:roster"/>';
            return XMPPSend::iq($this, "get", $payload, false, $this->jid, $callback);
        }
        
        /**
         * Add a new jabber account in connected Jaxl instance roster list
         *
         * @param string $jid JID to be added in the roster list
         * @param string $group Group name
         * @param bool|string $name Name of JID to be added
        */
        function addRoster($jid, $group, $name=false) {
            $payload = '<query xmlns="jabber:iq:roster">';
            $payload .= '<item jid="'.$jid.'"';
            if($name) $payload .= ' name="'.$name.'"';
            $payload .= '>';    
            $payload .= '<group>'.$group.'</group>';
            $payload .= '</item>';
            $payload .= '</query>';
            return XMPPSend::iq($this, "set", $payload, false, $this->jid, false);
        }
        
        /**
         * Update subscription of a jabber account in roster list
         *
         * @param string $jid JID to be updated inside roster list
         * @param string $group Updated group name
         * @param bool|string $subscription Updated subscription type
        */
        function updateRoster($jid, $group, $name=false, $subscription=false) {
            $payload = '<query xmlns="jabber:iq:roster">';
            $payload .= '<item jid="'.$jid.'"';
            if($name) $payload .= ' name="'.$name.'"';
            if($subscription) $payload .= ' subscription="'.$subscription.'"';
            $payload .= '>';
            $payload .= '<group>'.$group.'</group>';
            $payload .= '</item>';
            $payload .= '</query>';
            return XMPPSend::iq($this, "set", $payload, false, $this->jid, false);
        }
        
        /**
         * Delete a jabber account from roster list
         *
         * @param string $jid JID to be removed from the roster list
        */
        function deleteRoster($jid) {
            $payload = '<query xmlns="jabber:iq:roster">';
            $payload .= '<item jid="'.$jid.'" subscription="remove">';
            $payload .= '</item>';
            $payload .= '</query>';
            return XMPPSend::iq($this, "set", $payload, false, $this->jid, false);
        }
        
        /**
         * Send an XMPP message
         *
         * @param string $to JID to whom message is sent
         * @param string $message Message to be sent
         * @param string $from (Optional) JID from whom this message should be sent
         * @param string $type (Optional) Type of message stanza to be delivered
         * @param integer $id (Optional) Add an id attribute to transmitted stanza (omitted if not provided)
        */
        function sendMessage($to, $message, $from=false, $type='chat', $id=false) {
            $child = array();
            $child['body'] = $message;
            return XMPPSend::message($this, $to, $from, $child, $type, $id);
        }
        
        /** 
         * Send multiple XMPP messages in one go
         *
         * @param array $to array of JID's to whom this presence stanza should be send
         * @param array $from (Optional) array of JID from whom this presence stanza should originate
         * @param array $child (Optional) array of arrays specifying child objects of the stanza
         * @param array $type (Optional) array of type of presence stanza to send
         * @param integer $id (Optional) Add an id attribute to transmitted stanza (omitted if not provided)
        */
        function sendMessages($to, $from=false, $child=false, $type='chat', $id=false) {
            return XMPPSend::message($this, $to, $from, $child, $type);
        }

        /**
         * Send an XMPP presence stanza
         *
         * @param string $to (Optional) JID to whom this presence stanza should be send
         * @param string $from (Optional) JID from whom this presence stanza should originate
         * @param array $child (Optional) array specifying child objects of the stanza
         * @param string $type (Optional) Type of presence stanza to send
         * @param integer $id (Optional) Add an id attribute to transmitted stanza (omitted if not provided)
        */
        function sendPresence($to=false, $from=false, $child=false, $type=false, $id=false) {
           return XMPPSend::presence($this, $to, $from, $child, $type, $id);
        }

        /**
         * Send an XMPP iq stanza
         *
         * @param string $type Type of iq stanza to send
         * @param string $payload (Optional) XML string to be transmitted
         * @param string $to (Optional) JID to whom this iq stanza should be send
         * @param string $from (Optional) JID from whom this presence stanza should originate
         * @param string|array $callback (Optional) Callback method which will handle "result" type stanza rcved
         * @param integer $id (Optional) Add an id attribute to transmitted stanza (auto-generated if not provided)
        */
        function sendIQ($type, $payload=false, $to=false, $from=false, $callback=false, $id=false) {
            return XMPPSend::iq($this, $type, $payload, $to, $from, $callback, $id); 
        }

        /**
         * Logs library core and application debug data into the log file
         *
         * @param string $log Datum to be logged
         * @param integer $level Log level for passed datum
        */
        function log($log, $level=1) {
            JAXLog::log($log, $level, $this);
        }

        /**
         * Instead of using jaxl_require method applications can use $jaxl->requires to include XEP's in PHP environment
         *
         * @param string $class Class name of the XEP to be included e.g. JAXL0045 for including XEP-0045 a.k.a. Multi-user chat
        */
        function requires($class) {
            jaxl_require($class, $this);
        }

        /**
         * Starts Jaxl Core
         *
         * This method should be called after Jaxl initialization and hook registration inside your application code
         * Optionally, you can pass 'jaxl_post_connect' and 'jaxl_get_auth_mech' response type directly to this method
         * In that case application code SHOULD NOT register callbacks to above mentioned hooks
         *
         * @param string $arg[0] Optionally application code can pre-choose what Jaxl core should do after establishing socket connection.
         *                            Following are the valid options:
         *                            a) startStream
         *                            b) startComponent
         *                            c) startBosh
        */
        function startCore($mode=false) {
            if($mode) {
                switch($mode) {
                    case 'stream':
                        JAXLPlugin::add('jaxl_post_connect', array($this, 'startStream'));
                        break;
                    case 'component':
                        JAXLPlugin::add('jaxl_post_connect', array($this, 'startComponent'));
                        break;
                    case 'bosh':
                        $this->startBosh();
                        break;
                    default:
                        break;
                }
            }

            if($this->mode == 'cli') {
                try {
                    if($this->connect()) {
                        while($this->stream) {
                            $this->getXML();
                        }
                    }
                }
                catch(Exception $e) {
                    die($e->getMessage);
                }
            
                /* Exit Jaxl after end of loop */
                exit;
            }
        }

        /**
         * Starts a socket server at 127.0.0.1:port
         *
         * startHTTPd converts Jaxl instance into a Jaxl socket server (may or may not be a HTTP server)
         * Same Jaxl socket server instance <b>SHOULD NOT</b> be used for XMPP communications.
         * Instead separate "new Jaxl();" instances should be created for such XMPP communications.
         *
         * @param integer $port Port at which to start the socket server
         * @param integer $maxq JAXLHTTPd socket server max queue
        */
        function startHTTPd($port, $maxq) {
            JAXLHTTPd::start(array(
                'port'  =>  $port,
                'maxq'  =>  $maxq
            ));
        }

        /**
         * Start instance in bosh mode
        */
        function startBosh() {
            $this->JAXL0206('startStream');
        }

        /**
         * Start instance in component mode
        */
        function startComponent($payload, $jaxl) {
            $this->JAXL0114('startStream', $payload);
        }

    }

?>
