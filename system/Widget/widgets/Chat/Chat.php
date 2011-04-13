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

class Chat extends WidgetBase
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

    function getNameFromJID($jid)
    {
        return substr($jid, 0, strpos($jid, '@'));
    }
    
	function onIncomingMessage($data)
	{
        RPC::call('movim_prepend',
                       'chatMessages',
                       RPC::cdata('<p class="message">%s: %s</p>',
                                       $this->getNameFromJID($data['from']),
                                       $data['body']));
	}
	
	function onIncomingActive($data)
	{
	    RPC::call('movim_fill',
                       'chatState',
                       RPC::cdata("<h3>%s's chat is active</h3>",
                                       $this->getNameFromJID($data['from'])));
	}
	
	function onIncomingComposing($data) {
	    RPC::call('movim_fill',
                       'chatState',
                       RPC::cdata('<h3>%s is composing</h3>',
                                       $this->getNameFromJID($data['from'])));
	}

	function onIncomingOnline($data)
	{
	    RPC::call('movim_fill',
                       'chatState',
                       RPC::cdata('<h3>%s is online</h3>',
                                       $this->getNameFromJID($data['from'])));
	}

    function ajaxSendMessage($to, $message)
    {
    	$user = new User();
		$xmpp = Jabber::getInstance($user->getLogin());
        $xmpp->sendMessage($to, $message);
    }

	function build()
	{
		?>
		<div id="chat">
            <div id="chatState">
               <h3><?php echo t('Chat'); ?></h3>
            </div>
            <div id="chatMessages">
            </div>
            <input type="text" id="chatInput" value="<?php echo t('Message'); ?>" onfocus="myFocus(this);" onblur="myBlur(this);" onkeypress="if(event.keyCode == 13) {<?php $this->callAjax('ajaxSendMessage', "getDest()", "getMessageText()");?>}"/>
            <input type="text" id="chatTo" value="<?php echo t('To'); ?>" onfocus="myFocus(this);" onblur="myBlur(this);" />
            <input type="button" id="chatSend" onclick="<?php $this->callAjax('ajaxSendMessage', "getDest()", "getMessageText()");?>" value="<?php echo t('Send');?>"/>
		</div>
		<?php

	}
}

?>
