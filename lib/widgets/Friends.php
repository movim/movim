<?php

/**
 * @file Chat.php
 * This file is part of MOVIM.
 * 
 * @brief A jabber chat widget.
 *
 * @author Guillaume Pasquet <etenil@etenilsrealm.nl>
 *
 * @version 1.0
 * @date 20 October 2010
 *
 * Copyright (C)2010 MOVIM project
 * 
 * See COPYING for licensing information.
 */

class Friends extends Widget
{
	private $user;
	private $friendslist;
	
	function __construct($external, &$user)
	{
		parent::__construct($external);
		$this->user = $user;
		$this->addjs("test.js");
	}

	function processList($message)
	{
		$this->friendslist = $message;
	}

	function ajaxStuff($whatever)
	{
		echo date('Y-m-d H:i:s') . '<br />';
	}
	
	function build()
	{

		/*
		  foreach($this->friendslist) {
		      Show friend.
		  }
		*/
                ?>
                <div id="friends">
                <?php
                	/*
                		A little example to get the vCard from the xmpp connector
                	*/
                	$xmpp = XMPPConnect::getInstance(User::getLogin()); // We get the instance of the connexion
                	$xmpp->getVCard(); // We send the vCard request
                	$vcard = $xmpp->getPayload(); // We return the result of the request
                	echo "<img src='data:image/png;base64,".$vcard['vCardPhotoBinVal']."' ><br />\n".
                		 $vcard['vCardFN'].'<br />'.$vcard['vCardNickname']."\n";
                		 
                	// We're displaying the roster
                	$xmpp->getRosterList();
                	$list = $xmpp->getPayload();
                	
                	echo "<br /><br /><h3>".t('Contacts')."</h3>".
                		 "<div id='tinylist'><ul>\n";
                	for($i=0;$i< sizeof($list["queryItemName"]); $i++) {
                		echo "<li><a href='".
                			 $list["queryItemJid"][$i].
                			 "'>".
                			 $list["queryItemName"][$i].
                			 " (".$list["queryItemGrp"][$i].")</a></li>";
                	}
                	echo "</ul></div>";
                ?>
                  <div class="friend">
                    tagada
					 <input type="button" onclick="<?php $this->callAjax('ajaxStuff', 'APPEND', "'testzone'", '3');?>" value="Click me" />
					 <div id="testzone">
					 </div>
                  </div>
                  <div class="friend">
                    pouet
                  </div>
                </div>
                <?php
                
	}
}

?>
