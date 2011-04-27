<?php

/**
 * @file Jabber.php
 * This file is part of MOVIM.
 *
 * @brief Wrapper around Titine to handle mid-level functionalities
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
//define('TITINE_COMPONENT_HOST', 'component.'.TITINE_HOST_DOMAIN);
//define('TITINE_COMPONENT_PASS', 'pass');

define('TITINE_COMPONENT_PORT', 5559);

define('TITINE_LOG_PATH', BASE_PATH . 'log/titine.log');
define('TITINE_LOG_EVENT', true);
define('TITINE_LOG_ROTATE', false);

define('TITINE_BASE_PATH', LIB_PATH . 'Titine/');
include(LIB_PATH . 'Titine/core/titine.class.php');

class Jabber
{
	private static $instance;
	private $titine;
	private $payload;

	/**
	 * Firing up basic parts of titine and setting variables.
	 */
	private function __construct($jid)
	{
		$userConf = Conf::getUserConf($jid);
		$serverConf = Conf::getServerConf();

        $sess = Session::start(APP_NAME);

		$sess->remove('jid'); // ???

		$this->titine = new TITINE(array(
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
		$this->titine->requires(array(
						 'TITINE0030', // Service Discovery
						 'TITINE0054', // VCard
						 'TITINE0115', // Entity Capabilities
						 'TITINE0133', // Service Administration
						 'TITINE0085', // Chat State Notification
						 'TITINE0092', // Software Version
						 'TITINE0203', // Delayed Delivery
						 'TITINE0202', // Entity Time
						 'TITINE0206'  // Jabber over Bosh
						 ));

		// Defining call-backs
        $this->titine->addPlugin('titine_post_auth', array(&$this, 'postAuth'));
        $this->titine->addPlugin('titine_post_auth_failure', array(&$this, 'postAuthFailure'));
        //$this->titine->addPlugin('titine_post_roster_update', array(&$this, 'postRosterUpdate'));
        $this->titine->addPlugin('titine_post_disconnect', array(&$this, 'postDisconnect'));
        $this->titine->addPlugin('titine_get_iq', array(&$this, 'handle'));
		$this->titine->addPlugin('titine_get_auth_mech', array(&$this, 'postAuthMech'));
        $this->titine->addPlugin('titine_get_message', array(&$this, 'getMessage'));
        $this->titine->addPlugin('titine_get_presence', array(&$this, 'getPresence'));
        $this->titine->addPlugin('titine_get_bosh_curl_error', array(&$this, 'boshCurlError'));
        $this->titine->addplugin('titine_get_empty_body', array(&$this, 'getEmptyBody'));
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
				self::$instance = new Jabber($jid);
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
		 	throw new MovimException(t("Error: jid '%s' is incorrect", $jid));
		} else {
			$id = explode('@',$jid);
			$user = $id[0];
			$userConf = $id[1];
			$domain = $id[1];

			$this->titine->user = $user;
			$this->titine->pass = $pass;
			$this->titine->startCore('bosh');
		}

		self::setStatus(false, false);
	}

    public function postAuth() {
		//$this->titine->getRosterList();
		//$this->titine->getVCard();
    }

    public function postAuthFailure() {
    	$this->titine->shutdown();
    	throw new MovimException("Login error.");
    	$user = new User();
    	$user->desauth();
    }

    public function boshCurlError() {
//    	$this->titine->shutdown();
//    	throw new MovimException("Bosh connection error.");
//    	$user = new User();
//    	$user->desauth();
    }

	/*
	 * Auth mechanism (default : MD5)
	 */

	public function postAuthMech($mechanism) {$this->titine->auth('DIGEST-MD5');}

	/**
	 * Logs out
	 */

	public function logout()
	{
		define('TITINE_CURL_ASYNC', true);
		$this->titine->TITINE0206('endStream');
	}

	public function postDisconnect($data)
	{
		$evt = new Event();
		$evt->runEvent('postdisconnected', $data);
	}

	/**
	 * Pings the server. This must be done regularly in order to keep the
	 * session running.
	 */
	public function pingServer()
	{
		define('TITINE_CURL_ASYNC', false);
        $this->titine->TITINE0206('ping');
	}

	public function getEmptyBody($payload) {
        $evt = new Event();
        // Oooooh, am I disconnected??
        if(preg_match('/condition=[\'"]item-not-found[\'"]/', $payload)) {
            $evt->runEvent('serverdisconnect', null);
        } else {
            $evt->runEvent('incomingemptybody', 'ping');
        }
	}

	/**
	 * Envents handlers methods
	 */

	public function handle($payload) {
		$evt = new Event();
		if(isset($payload['vCard'])) { // Holy mackerel, that's a vcard!
			if(!is_null($payload['from'])) {
			   	Cache::c("vcard".$payload["from"], $payload);
				$evt->runEvent('vcardreceived', $payload);
			} else {
				Cache::c("myvcard", $payload);
				$evt->runEvent('myvcardreceived', $payload);
			}
		} elseif($payload['queryXmlns'] == "jabber:iq:roster") {
			Cache::c("roster", $payload);
            $evt->runEvent('rosterreceived', $payload);
		} else {
            $evt->runEvent('none', var_export($payload, true));
        }
    }

   	/*public function postRosterUpdate($payload) {
   		$evt = new Event();
		$evt->runEvent('rosterreceived', $payload);
   	}*/

	/* vCard methods
	 * Ask for a vCard and handle it
	 */

	public function getVCard($jid = false)
	{
		define('TITINE_CURL_ASYNC', true);
		$this->titine->TITINE0054('getVCard', $jid, $this->titine->jid, false);
	}

	/*
	 * Incoming messages
	 */

	public function getMessage($payloads) {
        foreach($payloads as $payload) {
            // reject offline message
            if($payload['offline'] != TITINE0203::$ns && $payload['type'] == 'chat') {

                $evt = new Event();

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
                $evt = new Event();

                //Cache::c('presence' . $payload['type'], $payload);

                if($payload['type'] == 'unavailable') {
                    if($payload['from'] == $this->titine->jid)
                        $evt->runEvent('postdisconnected', $data);
                    else
                        $evt->runEvent('incomeoffline', $payload);
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
		define('TITINE_CURL_ASYNC', true);
		$this->titine->getRosterList();
	}

	/**
	 * Sets the session's status.
	 */
	public function setStatus($status, $show)
	{
		define('TITINE_CURL_ASYNC', true);
		$this->titine->setStatus($status, $show, 41, true);
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
		define('TITINE_CURL_ASYNC', true);
		// Checking on the jid.
		if($this->checkJid($addressee)) {
			$this->titine->sendMessage($addressee, $body, false, 'chat');
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
			$this->titine->subscribe($jid);
			$this->titine->addRoster($jid, $contact, $alias);
		} else {
			throw new MovimException("Error: Incorrect JID `$jid'");
		}
	}

}

?>
