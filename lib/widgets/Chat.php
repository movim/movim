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

	function onIncomingChat($data)
	{
        $this->sendto('chatMessages', 'APPEND',
                      '<p>' . $data['from'] . ': ' . $data['body'] . '</p>');
	}

	function onIncomingPresence($data)
	{
		echo "onIncomingPresence was called. Message: $data";
	}

	function build()
	{
		?>
		<div id="chat">
            <div id="chatMessages">
            </div>
            <input type="text" id="chatInput" />
            <input type="button" id="chatSend" value="<?php echo t('Send');?>"/>
		</div>
		<?php

	}
}

?>
