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
//define('JAXL_COMPONENT_HOST', 'component.'.JAXL_HOST_DOMAIN);
//define('JAXL_COMPONENT_PASS', 'pass');

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
        $this->jaxl->addPlugin('jaxl_post_auth', array(&$this, 'postAuth'));
        $this->jaxl->addPlugin('jaxl_post_auth_failure', array(&$this, 'postAuthFailure'));
        //$this->jaxl->addPlugin('jaxl_post_roster_update', array(&$this, 'postRosterUpdate'));
        
        $this->jaxl->addPlugin('jaxl_get_iq', array(&$this, 'handle'));
		$this->jaxl->addPlugin('jaxl_get_auth_mech', array(&$this, 'postAuthMech'));
        $this->jaxl->addPlugin('jaxl_get_message', array(&$this, 'getMessage'));
        $this->jaxl->addPlugin('jaxl_get_presence', array(&$this, 'getPresence'));
        $this->jaxl->addPlugin('jaxl_get_bosh_curl_error', array(&$this, 'boshCurlError'));
        $this->jaxl->addplugin('jaxl_get_empty_body', array(&$this, 'getEmptyBody'));
	}

	public function getInstance($jid = false)
	{
		if(!is_object(self::$instance)) {
			if(!$jid) {
                $user = new User();
                if(!$user->isLogged()) {
                    throw new MovimException(t("Error: User not logged in."));
                } else {
                    $jid = $user->getLogin();
                    if($jid = "")
                        throw new MovimException(t("Error: JID not provided."));
                }
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
	
    public function postAuth() {
		$this->jaxl->getRosterList();
    }
    
    public function postAuthFailure() {
    	$this->jaxl->shutdown();
    	throw new MovimException("Login error.");
    	$user = new User();
    	$user->desauth();
    }
    
    public function boshCurlError() {
    	$this->jaxl->shutdown();
    	throw new MovimException("Bosh connection error.");
    	$user = new User();
    	$user->desauth();    
    }
	
	/*
	 * Auth mechanism (default : MD5)
	 */
	
	public function postAuthMech($mechanism) {$this->jaxl->auth('DIGEST-MD5');}

	/**
	 * Logs out
	 */
	 
	public function logout()
	{
		$this->jaxl->JAXL0206('endStream');
	}
	
    public function postDisconnect() {

    }
	
	/**
	 * Pings the server. This must be done regularly in order to keep the
	 * session running.
	 */
	public function pingServer()
	{
		define('CURL_ASYNC', false);
		$this->jaxl->JAXL0206('ping');
	}
	
	public function getEmptyBody($payload) {
   		$evt = new EventHandler();
		$evt->runEvent('incomingemptybody', 'ping');
	}
	
	/**
	 * Envents handlers methods
	 */
	
	public function handle($payload) {
		//movim_log($payload);
	
		$evt = new EventHandler();
		if(isset($payload['vCard'])) { // Holy mackerel, that's a vcard!
			$evt->runEvent('vcardreceived', $payload);
		} elseif($payload['queryXmlns'] == "jabber:iq:roster") {
            $evt->runEvent('rosterreceived', $payload);
		} else {
            $evt->runEvent('none', var_export($payload, true));
        }
    }

   	public function postRosterUpdate($payload) {
   		$evt = new EventHandler();
		$evt->runEvent('rosterreceived', $payload);
   	}

	/* vCard methods
	 * Ask for a vCard and handle it
	 */
	
	public function getVCard()
	{
		define('CURL_ASYNC', true);
		$this->jaxl->JAXL0054('getVCard', false, $this->jaxl->jid, false);
	}
	
	public function vcardReturn($payload)
	{
		$evt = new EventHandler();
		$evt->runEvent('vcardreceived', $payload);
	}

	/* 
	 * Incoming messages
	 */

	public function getMessage($payloads) {
        foreach($payloads as $payload) {
            // reject offline message
            if($payload['offline'] != JAXL0203::$ns && $payload['type'] == 'chat') {
            
                $evt = new EventHandler();
                
				if($payload['chatState'] == 'active' && $payload['body'] == NULL) {
					$evt->runEvent('incomeactive', $payload);
				} 
				elseif($payload['chatState'] == 'composing') {
                	$evt->runEvent('incomecomposing', $payload);
				}
				else {
					$evt->runEvent('incomemessage', $payload);
				}
            }

        }
	}
	
	/* 
	 * Incoming presences
	 */
	
	public function getPresence($payloads) {
        foreach($payloads as $payload) {
            if($payload['type'] == '' || in_array($payload['type'], array('available', 'unavailable'))) {
            
                $evt = new EventHandler();
                
            	if($payload['show'] == 'unavailable') {
					$evt->runEvent('incomeunavaiable', $payload);
            	} 
            	elseif($payload['show'] == 'away') {
					$evt->runEvent('incomeaway', $payload);
				} 
				elseif($payload['show'] == 'dnd') {
					$evt->runEvent('incomednd', $payload);
				} 
				else {
					$evt->runEvent('incomeonline', $payload);
				}
            }
        }
	}

	/**
	 * Fetches the roster's list and calls the provided processing function on
	 * roster's return.
	 * @param callback is a function that is called when the roster is returned
	 *   by the server.
	 */
	public function getRosterList()
	{
		define('CURL_ASYNC', true);
		$this->jaxl->getRosterList();
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
