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
	function WidgetLoad()
	{
		$this->registerEvent('incomechat', 'onIncomingChat');
		$this->registerEvent('incomepresence', 'onIncomingPresence');
	}

	function onIncomingChat($event)
	{
		echo "onIncomingChat was called. Message: $event";
	}

	function onIncomingPresence($event)
	{
		echo "onIncomingPresence was called. Message: $event";
	}
	
	function ajaxStuff($whatever)
	{
		//echo date('Y-m-d H:i:s') . '<br />';
		
		$user = new User();
        $xmpp = XMPPConnect::getInstance($user->getLogin());
		//$xmpp->sendMessage('edhelas@movim.eu', 'gna');

                	$xmpp->getVCard(); // We send the vCard request
                	$vcard = $xmpp->getPayload(); // We return the result of the request

                	echo "<img src='data:image/png;base64,".$vcard['vCardPhotoBinVal']."' ><br />\n".
                		 $vcard['vCardFN'].'<br />'.$vcard['vCardNickname']."\n";
		
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
                  <input type="button" id="chatSend" value="<?php echo t('Send');?>"/>
                  
                  <input type="button" onclick="<?php $this->callAjax('ajaxStuff', 'APPEND', "'testzone'", '3');?>" value="Message" />
		</div>
		<?php

	}
}

?>
