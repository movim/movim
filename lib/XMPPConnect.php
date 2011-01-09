<?php

/**
 * @file XMPPConnect.php
 * This file is part of MOVIM.
 * 
 * @brief Wrapper around Jaxl to handle mid-level functionalities
 *
 * @author Etenil <etenil@etenilsrealm.nl>
 *
 * @version 1.0
 * @date 13 October 2010
 *
 * Copyright (C)2010 Movim Project
 * 
 * See COPYING for licensing information.
 */
    
// Jabber external component setting
define('JAXL_COMPONENT_HOST', 'component.'.JAXL_HOST_DOMAIN);
define('JAXL_COMPONENT_PASS', 'pass');
define('JAXL_COMPONENT_PORT', 5559);

define('JAXL_LOG_PATH', BASE_PATH . 'log/jaxl.log');
define('JAXL_LOG_EVENT', true);
define('JAXL_LOG_ROTATE', false);
 
define('JAXL_BASE_PATH', LIB_PATH . 'Jaxl/'); 
include(LIB_PATH . 'Jaxl/core/jaxl.class.php');

class XMPPConnect
{
	private static $instance;
	private $jaxl;
	private $payload;
    
	/**
	 * Firing up basic parts of jaxl and setting variables.
	 */
	private function __construct($jid)
	{
		$userConf = GetConf::getUserConf($jid);
		$serverConf = GetConf::getServerConf();

		unset($_SESSION['jid']);
		
		$this->jaxl = new JAXL(array(
								   // User Configuration
								   'host' => $userConf['host'],
								   'domain' => isset($userConf['domain']) ? $userConf['domain'] : $userConf['host'],
								   'boshHost' => $userConf['boshHost'],
								   'boshSuffix' => $userConf['boshSuffix'],
								   'boshPort' => $userConf['boshPort'],
						   
								   // Server configuration
								   'boshCookieTTL' => $serverConf['boshCookieTTL'],
								   'boshCookiePath' => $serverConf['boshCookiePath'],
								   'boshCookieDomain' => $serverConf['boshCookieDomain'],
								   'boshCookieHTTPS' => $serverConf['boshCookieHTTPS'],
								   'boshCookieHTTPOnly' => $serverConf['boshCookieHTTPOnly'],
								   'logLevel' => $serverConf['logLevel'],
								   'boshOut'=>false,
								   
								   ));
		// Loading required XEPS
		$this->jaxl->requires(array(
						 'JAXL0030', // Service Discovery
						 'JAXL0054', // VCard
						 'JAXL0115', // Entity Capabilities
						 'JAXL0133', // Service Administration
						 'JAXL0085', // Chat State Notification
						 'JAXL0092', // Software Version
						 'JAXL0203', // Delayed Delivery
						 'JAXL0202', // Entity Time
						 'JAXL0206'  // XMPP over Bosh
						 ));

		// Defining call-backs
		$this->jaxl->addPlugin('jaxl_get_auth_mech', array(&$this, 'jaxl_get_auth_mech'));
        $this->jaxl->addPlugin('jaxl_post_auth', array(&$this, 'jaxl_post_auth'));
        $this->jaxl->addPlugin('jaxl_post_auth_failure', array(&$this, 'jaxl_post_auth_failure'));
        $this->jaxl->addPlugin('jaxl_get_message', array(&$this, 'getMessage'));
        //$this->jaxl->addPlugin('jaxl_get_empty_body', array(&$this, 'postEmptyBody'));
        $this->jaxl->addPlugin('jaxl_get_presence', array(&$this, 'getPresence'));
	}

    
    public function jaxl_post_auth($test, $test2) {

    }
    
    
    public function jaxl_post_auth_failure($test, $test2) {
    	$f = fopen(BASE_PATH."log/movim.log","w");
    	fwrite($f, "gna");
    	fclose($f);
    	/*var_dump($test);
    	var_dump($test2);
    	$this->jaxl->shutdown();
    	throw new MovimException("Login error.");
    	$user = new User();
    	$user->desauth();*/
    }

	public function jaxl_get_auth_mech($mechanism) {$this->jaxl->auth('DIGEST-MD5');}		//'ANONYMOUS');}

	
	public function getVCard()
	{
		$this->jaxl->JAXL0054('getVCard', false, $this->jaxl->jid, array(&$this, 'handlePayload'));
	}
	
	/**
	 * Pings the server. This must be done regularly in order to keep the
	 * session running.
	 */
	public function pingServer()
	{
		$this->jaxl->JAXL0206('ping');
	}
	
	function getMessage($payloads = 'test') {
        $html = '';
        foreach($payloads as $payload) {
            // reject offline message
            if($payload['offline'] != JAXL0203::$ns && $payload['type'] == 'chat') {
                if(strlen($payload['body']) > 0) {
                    $html .= '<span class="message"> '.$payload['from'].' : '.$payload['body']."</span><br />\n";
                }
                else if(isset($payload['chatState']) && in_array($payload['chatState'], JAXL0085::$chatStates)) {
                    $html .= '<span class="state"> '.$payload['from'].' chat state '.$payload['chatState']."</span><br />\n";
                }
            }
        }
        
        if($html != '') {
			$evt = new Event_handler();
			$evt->runEvent('incomechat', $html);
        }
	}
	
	function getPresence($payloads) {
        $html = '';
        foreach($payloads as $payload) {
            if($payload['type'] == '' || in_array($payload['type'], array('available', 'unavailable'))) {
                $html .= '<span class="presence">'.$payload['from'];
                if($payload['type'] == 'unavailable') $html .= ' is now offline</span><br />';
                else $html .= ' is now online</span><br />';
            }
        }
        if($html != '') {
			echo $html;
        }
	}
	
	/**
	 * handlePayload
	 * Save the payload
	 * 
	 * @param unknown $handler
	 * @return void
	 */
	function handlePayload($handler)
	{
		$this->payload = $handler;
	}
	
	/**
	 * getPayload
	 * Return the payload
	 * 
	 * @return void
	 */
	public function getPayload()
	{
		return $this->payload;
	}

	public function getInstance($jid = false)
	{
		if(!is_object(self::$instance)) {
			if(!$jid) {
				throw new MovimException("Error: JID not provided.");
			} else {
				self::$instance = new XMPPConnect($jid);
			}
		}
		return self::$instance;
	}

	/**
	 * Logs in
	 */
	public function login($jid, $pass)
	{
		if(!$this->checkJid($jid)) {
		 	throw new MovimException(sprintf(t("Error: jid '%s' is incorrect"), $jid));
		} else {
			$id = explode('@',$jid);
			$user = $id[0];
			$userConf = $id[1];
			$domain = $id[1];

			$this->jaxl->user = $user;
			$this->jaxl->pass = $pass;
			$this->jaxl->startCore('bosh');
		}
		
		self::setStatus(false, false);
	}

	/**
	 * Logs out
	 */
	public function logout()
	{
		$this->jaxl->JAXL0206('endStream');
	}

	/**
	 * Fetches the roster's list and calls the provided processing function on
	 * roster's return.
	 * @param callback is a function that is called when the roster is returned
	 *   by the server.
	 */
	public function getRosterList()
	{
		$this->jaxl->getRosterList(array(&$this, 'handlePayload'));
	}

	/**
	 * Sets the session's status.
	 */
	public function setStatus($status, $show)
	{
		$this->jaxl->setStatus($status, $show, 40, true);
	}

	private function checkJid($jid)
	{
		return true; /*
			preg_match('/^[a-zA-Z0-9_.-]+@[a-zA-Z0-9_.-]+\(?:.[a-z]{2,5})?$/',
					   $jid); */
	}

	/**
	 * Sends a message.
	 */
	public function sendMessage($addressee, $body)
	{
		// Checking on the jid.
		if($this->checkJid($addressee)) {
			$this->jaxl->sendMessage($addressee, $body, false, 'chat');
		} else {
			throw new MovimException("Error: Incorrect JID `$addressee'");
		}
	}

	/**
	 * Adds a contact to the roster.
	 */
	public function addContact($jid, $contact, $alias)
	{
		if($this->checkJid($jid)) {
			$this->jaxl->subscribe($jid);
			$this->jaxl->addRoster($jid, $contact, $alias);
		} else {
			throw new MovimException("Error: Incorrect JID `$jid'");
		}
	}

}

?>
