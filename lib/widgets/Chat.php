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

class Chat extends Widget
{
	private $user;
	
	function __construct(&$user)
	{
		$this->user = $user;
	}
	
	function build()
	{
		?>
		<div id="chat">
                  <div id="chatMessages">
		    <p>Tagada: blah blah blah</p>
		    <p>Pouet: Gna gna gna!</p>
                  </div>
                  <input type="text" id="chatInput" />
                  <input type="button" id="chatSend" value="<?php echo _('Send');?>"/>
		</div>
		<?php
	}
}

?>
