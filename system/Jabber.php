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
        $userConf = UserConf::getConf($jid);

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
                         'JAXL0107', // User Mood
                         'JAXL0118', // User Tune
						 'JAXL0115', // Entity Capabilities
						 'JAXL0133', // Service Administration
						 'JAXL0085', // Chat State Notification
						 'JAXL0092', // Software Version
						 'JAXL0203', // Delayed Delivery
						 'JAXL0202', // Entity Time
						 'JAXL0206', // Jabber over Bosh
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
	static public function getInstance($jid = false)
	{
		if(!is_object(self::$instance)) {
			if(!$jid) {
                $user = new User();
                if(!$user->isLogged()) {
                    return false;
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

    function destroy()
    {
        self::$instance = null;
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
		if(!checkJid($jid)) {
		 	throw new MovimException(t("jid '%s' is incorrect", $jid));
		} else {
			$id = explode('@',$jid);
			$user = $id[0];

			$this->jaxl->user = $user;
			$this->jaxl->pass = $pass;
			$this->jaxl->startCore('bosh');
		}
        
        self::setStatus(t('Connecting...'), false, false, true);  
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
	 * Return the current Cleaned Jid
	 *
	 * @return string
	 */
	public function getCleanJid() {
	    $jid = $this->jaxl->jid;
	    $res = JAXLUtil::splitJid($jid);
	    return $res[0].'@'.$res[1];
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
        $payload = $payload['movim'];
        
		global $sdb;
		$evt = new Event();
        
        // Holy mackerel, that's a vcard!
		if(is_array($payload['vCard']) &&
           $payload['@attributes']['type'] != 'error') 
        {
            $c = new ContactHandler();
                
            // If the vcard is mine
			if($payload['@attributes']['from'] == $this->getCleanJid() || 
               $payload['@attributes']['from'] == NULL ) 
            {
                $contact = $c->get($this->getCleanJid());
                $contact->setContact($payload);			            
                $sdb->save($contact); 
				$evt->runEvent('myvcard', $payload);
			}
            
	        // Yo it's your vcard dude !
            elseif(isset($payload['@attributes']['from'])) {
                $contact = $c->get($payload['@attributes']['from']);
                $contact->setContact($payload);			            
                $sdb->save($contact); 
                $evt->runEvent('vcard', $contact);
			}
		}
		
		// Roster case
		elseif(
            isset($payload['query']['item'][0]['@attributes']['subscription']) ||
            isset($payload['query']['item']['@attributes']['subscription'])
        ) {
		    if($payload['@attributes']['type'] == "result") {
		        global $sdb;
		        
		        foreach($payload['query']['item'] as $item) {
		            // If we've got only one item in the roster we use it as the only one
		            if(isset($item['subscription']))
		                $item = $payload['query']['item'];
                        
                    $c = new ContactHandler();
                    $contact = $c->get($item['@attributes']['jid']);
                    $contact->setContactRosterItem($item);	
                    $sdb->save($contact); 
		        }

                $evt->runEvent('roster', $payload);
            } elseif($payload['type'] == "set") {
                $this->getRosterList();
            }
        }
        elseif(isset($payload['pubsub']) && !(isset($payload['error']))) {
            list($xmlns, $parent) = explode("/", $payload['pubsub']['items']['@attributes']['node']);

            foreach($payload['pubsub']['items']['item'] as $item) {
                if(isset($item['id']))
                    $item = $payload['pubsub']['items']['item'];
                    
                if(isset($item['@attributes'])) {    
                    $c = new PostHandler();
                    $post = $c->get($item['@attributes']['id']);
                    
                    if($xmlns == 'urn:xmpp:microblog:0')
                        $post->setPost($item, $payload['@attributes']['from']);
                    elseif($xmlns == 'urn:xmpp:microblog:0:comments')
                        $post->setPost($item, $payload['@attributes']['from'], $parent);
                        
                    $sdb->save($post);  
                    
                    if($xmlns == 'urn:xmpp:microblog:0')
                        $evt->runEvent('stream', $payload);
                    elseif($xmlns == 'urn:xmpp:microblog:0:comments')
                        $evt->runEvent('comment', $parent);
                }
            }
        }
        else {
            $evt->runEvent('none', var_export($payload, true));
        }
        
        $evt->runEvent('incomingemptybody', 'ping');
    }

    /**
	 * Message handler
	 *
	 * @param array $payloads
	 * @return void
	 */
	public function getMessage($payloads) {
        $evt = new Event();
        
        foreach($payloads as $payload) {

            if($payload['offline'] != JAXL0203::$ns && $payload['type'] == 'chat') { // reject offline message

				if($payload['chatState'] == 'active' && $payload['body'] == NULL) {
					$evt->runEvent('incomeactive', $payload);
				}
				elseif($payload['chatState'] == 'composing') {
                	$evt->runEvent('composing', $payload);
				}
				elseif($payload['chatState'] == 'paused') {
                	$evt->runEvent('paused', $payload);
				}
				else {
					$evt->runEvent('message', $payload);
				}
            } elseif($payload['movim']['event']['items']['@attributes']['node'] == 'urn:xmpp:microblog:0') {
                $payload = $payload['movim'];
                
                global $sdb;
                movim_log($payload);
                $c = new PostHandler();
                $post = $c->get($payload['event']['items']['item']['@attributes']['id']);
                $post->setPost($payload['event']['items']['item'], $payload['@attributes']['from'], false, $this->getCleanJid());
                $sdb->save($post); 
            
                if($new == false) {
		            $sess = Session::start(APP_NAME);
                    if($sess->get('currentcontact') == $payload['@attributes']['from']) {
                        $evt->runEvent('currentpost', $payload);
                    }
                    
                    $evt->runEvent('post', $payload);
                }	            
            }
        }

        $evt->runEvent('incomingemptybody', 'ping');
	}

    /**
	 * Presence handler
	 *
	 * @param array $payloads
	 * @return void
	 */
	public function getPresence($payloads) {
		global $sdb;
        $evt = new Event();
		
        foreach($payloads as $payload) {
            $payload = $payload['movim'];
    		if($payload['@attributes']['type'] == 'subscribe') {
        		$evt->runEvent('subscribe', $payload);
    		} elseif($payload['@attributes']['type'] == 'result') {
    		
    		} elseif(in_array($payload['@attributes']['type'], array('available', 'unavailable', '', 'error'))) {
    		    
    		    // We update the presences
                list($jid, $ressource) = explode('/',$payload['@attributes']['from']);
                
    		    // We ask for the entity-capabilities
    		    if(isset($payload['c'])) {
		            $this->jaxl->JAXL0030(
		                'discoInfo', 
		                $payload['@attributes']['from'], 
		                $this->jaxl->jid, 
		                false, 
		                $payload['c']['@attributes']['node'].'#'.$payload['c']['@attributes']['ver']
		            );
    		    }
                
	            $presence = $sdb->select('Presence', array(
	                                                    'key' => $this->getCleanJid(), 
	                                                    'jid' => $jid,
	                                                    'ressource' => $ressource
	                                                    ));
	            if($presence == false) {
	                $presence = new Presence();
	                $presence->setPresence($payload);
	                $sdb->save($presence);
	            } else {
	                $presence = new Presence();
	                $sdb->load($presence, array(
                                            'key' => $this->getCleanJid(), 
                                            'jid' => $jid,
                                            'ressource' => $ressource
                                            ));
	                $presence->setPresence($payload);
	                $sdb->save($presence);
	            }
	            
	            if($payload['@attributes']['from'] == $payload['@attributes']['to']) 
	                $evt->runEvent('mypresence', $presence);

		        $evt->runEvent('presence', $presence);
            }
        }

        $evt->runEvent('incomingemptybody', 'ping');
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
	 * Create personnal microblog node
	 *
	 * @return void
	 */
	
	public function createNode()
	{
	    $this->jaxl->JAXL0277('createNode', $this->getCleanJid());
	}
	
	/**
	 * Subscribe to a node
	 *
	 * @param unknown $jid = false
	 * @return void
	 */
	
	public function subscribeNode($jid)
	{
	    $this->jaxl->JAXL0277('subscribeNode', $this->getCleanJid(), $jid);
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

    /**
	 * Get some items about a node
	 *
	 * @param string $pod
	 * @param string $node
	 * @return void
	 */   
	public function discoItems($pod, $node)
	{
		$this->jaxl->JAXL0060('getNodeItems', $pod, $this->jaxl->jid, $node);
	}
	
	/**
	 * Publish an item on microblog feed
	 *
	 * @param string $content
	 * @return void
	 */
	public function publishItem($content)
	{
	    $this->jaxl->JAXL0277('publishItem', $this->getCleanJid() ,$content);
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
		$this->jaxl->setStatus($status, $show, 41, false);
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
		if(checkJid($addressee)) {
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
		if(checkJid($jid)) {
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
		if(checkJid($jid)) {
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
		if(checkJid($jid)) {
			$this->jaxl->subscribe($jid);
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
		if(checkJid($jid)) {
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
