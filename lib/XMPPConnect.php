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
  // Connecting user credentials
define('JAXL_USER_NAME', 'user');
define('JAXL_USER_PASS', 'pass');
    
// Connecting jabber server details
define('JAXL_HOST_NAME', 'movim.eu');
define('JAXL_HOST_PORT', 5222);
define('JAXL_HOST_DOMAIN', 'movim.eu');
    
// Bosh connection manager details
define('JAXL_BOSH_HOST', 'natsu.upyum.com');
define('JAXL_BOSH_PORT', 80);
define('JAXL_BOSH_SUFFIX', 'http-bind');
    
// Bosh specific browser cookie settings
define('JAXL_BOSH_COOKIE_PATH', '/');
// cookie domain should be "false" (without quotes) if bosh application domain is like localhost, xmpp, jaxl etc (single word)
// otherwise of type ".localhost.localdomain" (with quotes) if bosh application domain is like localhost.localdomain, jaxl.im 
define('JAXL_BOSH_COOKIE_DOMAIN', false);
define('JAXL_BOSH_COOKIE_TTL', 3600);
define('JAXL_BOSH_COOKIE_HTTPS', false);
define('JAXL_BOSH_COOKIE_HTTP_ONLY', true);
    
// Jabber external component setting
define('JAXL_COMPONENT_HOST', 'component.'.JAXL_HOST_DOMAIN);
define('JAXL_COMPONENT_PASS', 'pass');
define('JAXL_COMPONENT_PORT', 5559);

define('JAXL_LOG_PATH', BASE_PATH . 'log/jaxl.log');
define('JAXL_LOG_EVENT', true);
define('JAXL_LOG_LEVEL', 5);
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
		$host = GetConf::getHostConf($jid);
		//print_r($host);
		unset($_SESSION['jid']);
		$this->jaxl = new JAXL(array(
								   'host' => $host['host'],
								   'domain' => $host['host'],
								   'boshHost' => $host['boshHost'],
								   'boshSuffix' => $host['boshSuffix'],
								   'logLevel' => 5,
								   'boshOut'=>false,
								   ));

		// Loading required XEPS
		$this->jaxl->requires(array(
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
		JAXLPlugin::add('jaxl_get_auth_mech', array(&$this, 'jaxl_get_auth_mech'));
        JAXLPlugin::add('jaxl_post_auth', array(&$this, 'jaxl_post_auth'));

	}
	
	/**
	 * Defining Callback functions
	 */
	/*function handleVCard($payload) {
        echo "<b>Successfully fetched VCard</b><br/>";
        print_r($payload);
        $this->jaxl->JAXL0206('endStream');
    }*/

	public function jaxl_get_auth_mech($mechanism) {$this->jaxl->auth('DIGEST-MD5');}		//'ANONYMOUS');}

	/*public function jaxl_post_auth() {
		$this->jaxl->JAXL0054('getVCard', false, $this->jaxl->jid, array(&$this, 'handleVCard'));
	}*/
	
	public function getVCard()
	{
		$this->jaxl->JAXL0054('getVCard', false, $this->jaxl->jid, array(&$this, 'handlePayload'));
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
		$this->jaxl->JAXL0206('endStream');
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
		 	throw new MovimException(sprintf(_("Error: jid `%s' is incorrect"), $jid));
		} else {
			$id = explode('@',$jid);
			$user = $id[0];
			$host = $id[1];
			$domain = $id[1];

			$this->jaxl->user = $user;
			$this->jaxl->pass = $pass;
			$this->jaxl->startCore('bosh');
		}
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
	public function getRosterList($callback)
	{
		$this->jaxl->getRosterList($callback);
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

	/**
	 * Pings the server. This must be done regularly in order to keep the
	 * session running.
	 */
	public function pingServer()
	{
		$this->jaxl->JAXL0206('ping');
	}
}

?>
