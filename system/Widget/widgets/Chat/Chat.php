<?php

/**
 * @package Widgets
 *
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
    	$this->addcss('chat.css');
    	$this->addjs('chat.js');
		$this->registerEvent('incomemessage', 'onMessage');
		$this->registerEvent('incomecomposing', 'onComposing');
		$this->registerEvent('incomepaused', 'onPaused');

	    if(Cache::c('activechat') == false)
	        Cache::c('activechat', array());
	}

	function ajaxOpenTalk($jid) {
	    $talks = Cache::c('activechat');
	    if(!array_key_exists($jid, $talks)) {
            RPC::call('movim_prepend',
                           'talks',
                           RPC::cdata($this->prepareTalk($jid, true)));
            $talks[$jid] = true;
            Cache::c('activechat', $talks);
            RPC::commit();
        }
	}

	function ajaxCloseTalk($jid) {
	    $talks = Cache::c('activechat');
	    unset($talks[$jid]);
	    Cache::c('activechat', $talks);
	}

    function ajaxSendMessage($to, $message)
    {
    	$user = new User();
		$xmpp = Jabber::getInstance($user->getLogin());
        $xmpp->sendMessage($to, $message);
        Logger::log(Logger::LOGLEVEL_STANDARD, "Sent '$message' to $to");
    }

	function onMessage($data)
	{
	    $talks = Cache::c('activechat');
	    list($jid) = explode('/', $data['from']);

	    if(!array_key_exists($jid, $talks)) {
            RPC::call('movim_prepend',
                           'talks',
                           RPC::cdata($this->prepareTalk($jid, true)));
	        $talks[$jid] = true;
	        Cache::c('activechat', $talks);
	    }

        RPC::call('movim_fill',
                       $jid.'Tab',
                       RPC::cdata($jid));

        RPC::call('movim_prepend',
                       $jid.'Messages',
                       RPC::cdata('<p class="message"><span class="date">'.date('G:i', time()).'</span>'.htmlentities($data['body'], ENT_COMPAT, "UTF-8").'</p>'));
	}

	function onComposing($data)
	{
		list($jid) = explode('/', $data['from']);
        RPC::call('movim_fill',
                       $jid.'Tab',
                      t('Composing'));
	}

	function onPaused($data)
	{
		list($jid) = explode('/', $data['from']);
        RPC::call('movim_fill',
                       $jid.'Tab',
                       t('Paused'));
	}

	public function prepareTalk($jid, $new = false) {

	    if($new)
	        $style = ' style="display: block" ';
	    else
	        $style = '';
	    return '
            <div class="talk">
                <div class="box" id="'.$jid.'Box" '.$style.'>
                <div class="messages" id="'.$jid.'Messages"></div>
                <input type="text" class="input" value="'.t('Message').'" onfocus="myFocus(this);" onblur="myBlur(this);" onkeypress="if(event.keyCode == 13) {'.$this->genCallAjax('ajaxSendMessage', "'".$jid."'", "sendMessage(this, '".$jid."')").'}"/>
                </div>

                <span class="tab" id="'.$jid.'Tab" onclick="showTalk(this);">'.$jid.'</span>
                <span class="cross" onclick="'.$this->genCallAjax("ajaxCloseTalk", "'".$jid."'").' closeTalk(this)"></span>
            </div>
	    ';
	}

	function build()
	{
	    $talks = Cache::c('activechat');
		?>
		    <div id="talks">
		        <?php foreach($talks as $key => $value){
		            echo $this->prepareTalk($key);
		        } ?>
		    </div>
		<?
	}
}
