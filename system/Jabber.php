<?php

/**
 * @file Jabber.php
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

define('JAXL_COMPONENT_PORT', 5559);

define('JAXL_LOG_PATH', BASE_PATH . 'log/jaxl.log');
define('JAXL_LOG_EVENT', true);
define('JAXL_LOG_ROTATE', false);

define('JAXL_BASE_PATH', LIB_PATH . 'Jaxl/');
include(LIB_PATH . 'Jaxl/core/jaxl.class.php');

class Jabber
{
	private static $instance;
	private $jaxl;
	private $payload;

	/**
	 * Firing up basic parts of jaxl and setting variables.
	 */
	private function __construct($jid)
	{
		$userConf = Conf::getUserConf($jid);
		$serverConf = Conf::getServerConf();

        $sess = Session::start(APP_NAME);

		$sess->remove('jid'); // ???

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
                         'JAXL0060', // Pubsub
						 'JAXL0115', // Entity Capabilities
						 'JAXL0133', // Service Administration
						 'JAXL0085', // Chat State Notification
						 'JAXL0092', // Software Version
						 'JAXL0203', // Delayed Delivery
						 'JAXL0202', // Entity Time
						 'JAXL0206',  // Jabber over Bosh
						 'JAXL0277'  // Microblogging
						 ));

		// Defining call-backs
		
		// Connect-Disconnect
        $this->jaxl->addPlugin('jaxl_post_auth', array(&$this, 'postAuth'));
        $this->jaxl->addPlugin('jaxl_post_auth_failure', array(&$this, 'postAuthFailure'));
        //$this->jaxl->addPlugin('jaxl_post_roster_update', array(&$this, 'postRosterUpdate'));
        $this->jaxl->addPlugin('jaxl_post_disconnect', array(&$this, 'postDisconnect'));
		$this->jaxl->addPlugin('jaxl_get_auth_mech', array(&$this, 'postAuthMech'));
		
		// The handlers
        $this->jaxl->addPlugin('jaxl_get_iq', array(&$this, 'getIq'));
        $this->jaxl->addPlugin('jaxl_get_message', array(&$this, 'getMessage'));
        $this->jaxl->addPlugin('jaxl_get_presence', array(&$this, 'getPresence'));
        
        // Others hooks
        $this->jaxl->addPlugin('jaxl_get_bosh_curl_error', array(&$this, 'boshCurlError'));
        $this->jaxl->addplugin('jaxl_get_empty_body', array(&$this, 'getEmptyBody'));
	}
	
	/**
	 * Get the current instance
	 *
	 * @param string $jid = false
	 * @return instance
	 */
	public function getInstance($jid = false)
	{
		if(!is_object(self::$instance)) {
			if(!$jid) {
                $user = new User();
                if(!$user->isLogged()) {
                    throw new MovimException(t("User not logged in."));
                } else {
                    $jid = $user->getLogin();
                    if($jid = "")
                        throw new MovimException(t("JID not provided."));
                }
			} else {
				self::$instance = new Jabber($jid);
			}
		}
		return self::$instance;
	}
	
    /**
	 * Start the BOSH connection
	 *
	 * @param string $jid
	 * @param string $pass
	 * @return void
	 */
	public function login($jid, $pass)
	{
		if(!$this->checkJid($jid)) {
		 	throw new MovimException(t("jid '%s' is incorrect", $jid));
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
     * postAuth
     *
     * @return void
     */
    public function postAuth() {
		//$this->jaxl->getRosterList();
		//$this->jaxl->getVCard();
    }

    /**
     * postAuthFailure
     *
     * @return void
     */
    public function postAuthFailure() {
    	$this->jaxl->shutdown();
    	throw new MovimException("Login error.");
    	$user = new User();
    	$user->desauth();
    }
    
    /**
	 * Return the current ressource
	 *
	 * @return string
	 */	
	public function getResource()
	{
	    $res = JAXLUtil::splitJid($this->jaxl->jid);
	    return $res[2];
	}
	
	/**
	 * Return the current Jid
	 *
	 * @return string
	 */
	public function getJid() {
	    return $this->jaxl->jid;
	}


    public function boshCurlError() {
//    	$this->jaxl->shutdown();
//    	throw new MovimException("Bosh connection error.");
//    	$user = new User();
//    	$user->desauth();
    }

    /**
	 * Auth mechanism
	 *
	 * @param array $mechanism
	 * @return void
	 */
	public function postAuthMech($mechanism) {
	    movim_log($mechanism);
        if(in_array("DIGEST-MD5", $mechanism))
            $this->jaxl->auth('DIGEST-MD5');
        elseif(in_array("PLAIN", $mechanism))
            $this->jaxl->auth('PLAIN');
	}

    /**
	 * Close the BOSH connection
	 *
	 * @return void
	 */
	public function logout()
	{
		$this->jaxl->JAXL0206('endStream');
	}

    /**
	 * postDisconnect
	 *
	 * @param array $data
	 * @return void
	 */
	public function postDisconnect($data)
	{
		$evt = new Event();
		$evt->runEvent('postdisconnected', $data);
	}

	/**
	 * Pings the server. This must be done regularly in order to keep the
	 * session running
	 *
	 * @return void
	 */
	public function pingServer()
	{
        $this->jaxl->JAXL0206('ping');
	}

    /**
	 * Get an empty body
	 *
	 * @param array $payload
	 * @return void
	 */
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
	 * Iq handler
	 * 
	 * @param array $payload
	 * @return void
	 */
	public function getIq($payload) {
	    movim_log($payload);
		$evt = new Event();
		
		// vCard case
		if(isset($payload['vCard'])) { // Holy mackerel, that's a vcard!
			if($payload['from'] == reset(explode("/", $payload['to'])) || $payload['from'] == NULL) {
				Cache::c("myvcard", $payload);
				$evt->runEvent('myvcardreceived', $payload);
			} else {
			   	Cache::c("vcard".$payload["from"], $payload);
			   	$res = JAXLUtil::splitJid($payload['to']);
				Conf::savePicture($res[0].'@'.$res[1], $payload['from'], $payload['vCardPhotoBinVal'], $payload["vCardPhotoType"]);
				$evt->runEvent('vcardreceived', $payload);
			}
		}
		
		// Roster case
		elseif($payload['queryXmlns'] == "jabber:iq:roster") {
		    if($payload['type'] == "result") {
			    Cache::c("roster", $payload);
                $evt->runEvent('rosterreceived', $payload);
            } elseif($payload['type'] == "set") {
                $this->getRosterList();
            }
        } 
        
        // Pubsub node case
        elseif($payload["pubsubNode"] ==  "urn:xmpp:microblog:0") {
            $evt->runEvent('streamreceived', $payload);
		}
		
		elseif(isset($payload["pubsubNode"])) {
            $evt->runEvent('thread', $payload);
		}
        
        elseif($payload["queryXmlns"] == "http://jabber.org/protocol/disco#items") {
            $evt->runEvent('disconodes', $payload);
        } else {
            $evt->runEvent('none', var_export($payload, true));
        }
    }
    
    /**
	 * Message handler
	 *
	 * @param array $payloads
	 * @return void
	 */
	public function getMessage($payloads) {
	    movim_log($payloads);
        foreach($payloads as $payload) {
            
            if($payload['offline'] != JAXL0203::$ns && $payload['type'] == 'chat') { // reject offline message

                $evt = new Event();

				if($payload['chatState'] == 'active' && $payload['body'] == NULL) {
					$evt->runEvent('incomeactive', $payload);
				}
				elseif($payload['chatState'] == 'composing') {
                	$evt->runEvent('incomecomposing', $payload);
				}
				elseif($payload['chatState'] == 'paused') {
                	$evt->runEvent('incomepaused', $payload);
				}
				else {
					$evt->runEvent('incomemessage', $payload);
				}
            }

        }
	}

    /**
	 * Presence handler
	 *
	 * @param array $payloads
	 * @return void
	 */
	public function getPresence($payloads) {
        foreach($payloads as $payload) {

    		if($payload['type'] == 'subscribe') {
        		$evt = new Event();
        		$evt->runEvent('incomesubscribe', $payload);
    		} elseif($payload['type'] == 'result') {
    		
    		} elseif($payload['type'] == '' || in_array($payload['type'], array('available', 'unavailable'))) {

                // We create the events
                $evt = new Event();

				$evt->runEvent('incomepresence', $payload);
				
				if($payload['from'] == $this->getJid())
					$evt->runEvent('incomemypresence', $payload);

                // We update the presence array
                $session = Session::start(APP_NAME);
				$presences = $session->get('presences');
				
				list($jid, $ressource) = explode('/',$payload['from']);
				
				if(!is_array($presences[$jid]))
				    $presences[$jid] = array();
				    
				$presences[$jid]['status'] = $payload['status'];
				
                if($payload['type'] == 'unavailable') {
                    if($payload['from'] == $this->jaxl->jid)
                        $evt->runEvent('postdisconnected', $data);
                    else {
                        $presences[$jid][$ressource] = 4;
                        $evt->runEvent('incomeoffline', $payload);
                    }
                }
                elseif($payload['show'] == 'xa') {
                    $presences[$jid][$ressource] = 5;
                    $evt->runEvent('incomeaway', $payload);

                }
                elseif($payload['show'] == 'away') {
                    $presences[$jid][$ressource] = 3;
                    $evt->runEvent('incomeaway', $payload);

                }
                elseif($payload['show'] == 'dnd') {
                    $presences[$jid][$ressource] = 2;
                    $evt->runEvent('incomednd', $payload);
                }
                else {
                    $presences[$jid][$ressource] = 1;
                    $evt->runEvent('incomeonline', $payload);
                }
				$session->set('presences', $presences);
            }
        }
	}

   	/*public function postRosterUpdate($payload) {
   		$evt = new Event();
		$evt->runEvent('rosterreceived', $payload);
   	}*/

    /**
	 * Ask for a vCard
	 *
	 * @param string $jid = false
	 * @return void
	 */
	public function getVCard($jid = false)
	{
		$this->jaxl->JAXL0054('getVCard', $jid, $this->jaxl->jid, false);
	}
	
	/**
	 * sendVcard
	 *
	 * @param array $vcard
	 * @return void
	 */
	public function updateVcard($vcard)
	{
		$this->jaxl->JAXL0054('updateVCard', $vcard);
        $this->jaxl->JAXL0054('getVCard', false, $this->jaxl->jid, false);
	}
	
	/**
	 * Ask for some items
	 *
	 * @param unknown $jid = false
	 * @return void
	 */
	public function getWall($jid = false) {
		$this->jaxl->JAXL0277('getItems', $jid);
	}
	
	/**
	 * Ask for some comments of an article
	 *
	 * @param string $jid
	 * @param string $id
	 * @return void
	 */
	public function getComments($jid, $id) {
		$this->jaxl->JAXL0277('getComments', 'pubsub.jappix.com', $id);
	}
	
    /**
	 * Service Discovery
	 *
	 * @param string $jid = false
	 * @return void
	 */
	public function discover($jid = false)
	{
		//$this->jaxl->JAXL0030('discoInfo', $jid, $this->jaxl->jid, false, false);
		//$this->jaxl->JAXL0030('discoItems', $jid, $this->jaxl->jid, false, false);mov
		$this->jaxl->JAXL0277('getItems', 'edhelas@jappix.com');
        //psgxs.linkmauve.fr
	}
	
	public function discoNodes($pod)
	{
		$this->jaxl->JAXL0060('discoNodes', $pod, $this->jaxl->jid);  
	}
	
	public function discoItems($pod, $node)
	{
		$this->jaxl->JAXL0060('getNodeItems', $pod, $this->jaxl->jid, $node);  
	}

    /**
	 * Ask for the roster
	 *
	 * @return void
	 */
	public function getRosterList()
	{
		$this->jaxl->getRosterList();
	}

    /**
	 * Set a new status
	 *
	 * @param string $status
	 * @param string $show
	 * @return void
	 */
	public function setStatus($status, $show)
	{
		$this->jaxl->setStatus($status, $show, 41, true);
	}

    /**
	 * Check the current Jid
	 *
	 * @param string $jid
	 * @return bool
	 */
	private function checkJid($jid)
	{
		return true; /*
			preg_match('/^[a-zA-Z0-9_.-]+@[a-zA-Z0-9_.-]+\(?:.[a-z]{2,5})?$/',
					   $jid); */
	}

	/**
	 * Send a message
	 *
	 * @param string $addressee
	 * @param steirng $body
	 * @return void
	 */
	public function sendMessage($addressee, $body)
	{
		// Checking on the jid.
		if($this->checkJid($addressee)) {
			$this->jaxl->sendMessage($addressee, $body, false, 'chat');
		} else {
			throw new MovimException("Incorrect JID `$addressee'");
		}
	}
	
	/**
	 * Subscribe to a contact request
	 *
	 * @param unknown $jid
	 * @return void
	 */
	public function subscribedContact($jid) {
		if($this->checkJid($jid)) {
			$this->jaxl->subscribed($jid);
			$this->jaxl->addRoster($jid);
		} else {
			throw new MovimException("Incorrect JID `$jid'");
		}
	}

	/**
	 * Accecpt a new contact
	 *
	 * @param string $jid
	 * @param string $group
	 * @param string $alias
	 * @return void
	 */
	public function acceptContact($jid, $group, $alias)
	{
		if($this->checkJid($jid)) {
			$this->jaxl->addRoster($jid, $group, $alias);
			$this->jaxl->subscribe($jid);
		} else {
			throw new MovimException("Incorrect JID `$jid'");
		}
	}
	
	/**
	 * Add a new contact
	 *
	 * @param string $jid
	 * @param string $grJaxloup
	 * @param string $alias
	 * @return void
	 */
	public function addContact($jid, $group, $alias) {
		if($this->checkJid($jid)) {
			$this->jaxl->subscribe($jid);
			$this->jaxl->addRoster($jid, $group, $alias);
		} else {
			throw new MovimException("Incorrect JID `$jid'");
		}
	}
	
	/**
	 * Remove a contact
	 *
	 * @param string $jid
	 * @return void
	 */
	public function removeContact($jid) {
		if($this->checkJid($jid)) {
			$this->jaxl->deleteRoster($jid);
			$this->jaxl->unsubscribe($jid);
		} else {
			throw new MovimException("Incorrect JID `$jid'");
		}
	}
	
	/**
	 * Unsubscribe to a contact
	 *
	 * @param unknown $jid
	 * @return void
	 */
	public function unsubscribed($jid) {
		$this->jaxl->unsubscribed($jid);
	}

}

?>
