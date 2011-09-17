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

class Wall extends WidgetBase
{

    function WidgetLoad()
    {
    	$this->addcss('wall.css');
		$this->registerEvent('streamreceived', 'onStream');
    }
    
    function onStream($payload) {
        $html = '';
        
        movim_log($payload);
        if($payload['movim']['error']['@attributes']['code'] != 501) {
            $html .= '
                <a 
                    class="button tiny icon" 
                    href="#"
                    style="float: right;"
                    id="wallfollow" 
                    onclick="'.$this->genCallAjax('ajaxSubscribe', "'".$payload["movim"]["@attributes"]["from"]."'").'" 
                >
                    '.t('Follow').'
                </a>
                <br /><br />
                ';
        }
            
        $i = 0;
//        $user = new User();
//        $jid = $user->getLogin();
        foreach($payload["pubsubItemsEntryContent"] as $key => $value) {
            $html .= '
                <div class="message" id="'.$payload["pubsubItemsId"][$i].'">
				    <img class="avatar">

		        	<div class="content">
     			        <span>'.$payload["pubsubItemsEntryAuthor"][$i].'</span> <span class="date">'.date('j F - H:i',strtotime($payload["pubsubItemsEntryPublished"][$i])).'</span> '.$value.'
		        	</div>
		        	<div class="comment">
		        	<a href="#" onclick="'.$this->genCallAjax('ajaxGetComments', "'".$_GET['f']."'", "'".$payload["pubsubItemsId"][$i]."'").'">'.t('Get the comments').'</a>
		        	</div>
           		</div>';
            $i++;
        }
        
        if($html == '') 
            $html = t("Contact's feed cannot be loaded.");
        RPC::call('movim_fill', 'wall', RPC::cdata($html));
    }

	function ajaxWall($jid) {
		$xmpp = Jabber::getInstance();
		$xmpp->getWall($jid);
	}
	
	function ajaxSubscribe($jid) {
		$xmpp = Jabber::getInstance();
		$xmpp->subscribeNode($jid);
	}
	
	function ajaxGetComments($jid, $id) {
		$xmpp = Jabber::getInstance();
		$xmpp->getComments($jid, $id);
	}

	function build()
	{
		?>
		<div class="tabelem" id="wall" title="<?php echo t('Feed');?>">
            <script type="text/javascript">

            <?php echo 'setTimeout(\''.$this->genCallAjax('ajaxWall', '"'.$_GET['f'].'"').'\', 500);'; ?>
            </script>
            <?php echo t('Loading the contact feed ...'); ?>
       	</div>
		<?php
	}
}

?>
