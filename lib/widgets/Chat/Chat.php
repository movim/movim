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
        $this->addjs('chat.js');
        $this->addcss('chat.css');
		$this->registerEvent('incomemessage', 'onIncomingMessage');
		$this->registerEvent('incomeactive', 'onIncomingActive');
		$this->registerEvent('incomecomposing', 'onIncomingComposing');
		$this->registerEvent('incomeonline', 'onIncomingOnline');
	}

	function onIncomingMessage($data)
	{
	    $this->sendto('movim_prepend', array(
                          'chatMessages',
		                  $this->cdata('<p class="message">' . substr($data['from'], 0, strpos($data['from'], '@')) . ': ' . $data['body'] . '</p>'),
                          ));
	}
	
	function onIncomingActive($data)
	{
	    $this->sendto('movim_fill', array(
                          'chatState',
                          $this->cdata('<h3>'.substr($data['from'], 0, strpos($data['from'], '@')). "'s chat is active</h3>"),
                          ));
	}
	
	function onIncomingComposing($data) {
	    $this->sendto('movim_fill', array(
                          'chatState',
                          $this->cdata('<h3>'.substr($data['from'], 0, strpos($data['from'], '@')). " is composing</h3>"),
                          ));
	}

	function onIncomingOnline($data)
	{
	    $this->sendto('movim_fill', array(
                          'chatState',
                          $this->cdata('<h3>'.substr($data['from'], 0, strpos($data['from'], '@')). " is online</h3>"),
                          ));
	}

    function ajaxSendMessage($message, $to)
    {
		$xmpp = XMPPConnect::getInstance();
		$xmpp->sendMessage($to, $message);
    }

	function build()
	{
		?>
		<div id="chat">
            <div id="chatState">
            </div>
            <div id="chatMessages">
            </div>
            <input type="text" id="chatInput" value="Message" onfocus="myFocus(this);" onblur="myBlur(this);" onkeypress="if(event.keyCode == 13) {<?php $this->callAjax('ajaxSendMessage', 'movim_drop', "'test'", "getDest()", "getMessageText()");?>}"/>
            <input type="text" id="chatTo" value="To" onfocus="myFocus(this);" onblur="myBlur(this);" />
            <input type="button" id="chatSend" onclick="<?php $this->callAjax('ajaxSendMessage', 'movim_drop', "'test'", "getDest()", "getMessageText()");?>" value="<?php echo t('Send');?>"/>
		</div>
		<?php

	}
}

?>
