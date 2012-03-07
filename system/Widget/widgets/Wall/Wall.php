<?php

/**
 * @package Widgets
 *
 * @file Wall.php
 * This file is part of MOVIM.
 *
 * @brief The contact feed
 *
 * @author Jaussoin Timothée <edhelas_at_gmail_dot_com>
 *
 * @version 1.0
 * @date 30 september 2011
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
    	$this->addjs('wall.js');
		$this->registerEvent('streamreceived', 'onStream');
		$this->registerEvent('comments', 'onComments');
		$this->registerEvent('currentpost', 'onNewPost');
    }
    
    function onComments($parent) {        
        global $sdb;
        $message = $sdb->select('Message', array('key' => $this->user->getLogin(), 'nodeid' => $parent));

        $html = $prepareComments($message[0]);
        RPC::call('movim_fill', $parent.'comments', RPC::cdata($html));
    }
    
    function onNewPost($payload) {
        global $sdb;
        $message = $sdb->select('Message', array('nodeid' => $payload['event']['items']['item']['@attributes']['id']));
        $html = preparePost($message[0]);

        RPC::call('movim_prepend', 'wall', RPC::cdata($html));
    }
    
    function onStream($payload) {
        $html = '';

        if(isset($payload['error']))
            RPC::call('hideWall'); 
        else {
            $html .= '
                <!--<a 
                    class="button tiny icon" 
                    href="#"
                    style="float: right;"
                    id="wallfollow" 
                    onclick="'.$this->genCallAjax('ajaxSubscribe', "'".$payload["@attributes"]["from"]."'").'" 
                >
                    '.t('Follow').'
                </a>
                <br /><br />-->
                ';
            
            global $sdb;
            $messages = $sdb->select('Message', array('key' => $this->user->getLogin(), 'jid' => $payload["@attributes"]["from"]), 'updated', true);
            
            if($messages == false) {            
                RPC::call('hideWall'); 
            } else {
                $html = '';
                
                foreach(array_slice($messages, 0, 20) as $message) {
                    $html .= preparePost($message);
                }
                echo $html;
            }
        }

        RPC::call('movim_fill', 'wall', RPC::cdata($html));
    }


	function ajaxWall($jid) {
		$this->xmpp->getWall($jid);
	}
	
	function ajaxSubscribe($jid) {
		$this->xmpp->subscribeNode($jid);
	}
	
	function ajaxGetComments($jid, $id) {
		$this->xmpp->getComments($jid, $id);
	}

	function build()
	{
		?>
		<div class="tabelem protect orange" id="wall" title="<?php echo t('Feed');?>">
		        <!--<a 
                    class="button tiny icon follow" 
                    href="#"
                    style="float: right;"
                    onclick="<?php echo $this->callAjax('ajaxSubscribe', "'".$_GET['f']."'"); ?>" 
                >
                    <?php echo t('Follow'); ?>
                </a>
                <br /><br />-->
            <?php 
            global $sdb;
            $messages = $sdb->select('Message', array('key' => $this->user->getLogin(), 'jid' => $_GET['f'], 'parentid' => ''), 'updated', true);
            
            if($messages == false) {
            ?>
                <script type="text/javascript">
                <?php echo 'setTimeout(\''.$this->genCallAjax('ajaxWall', '"'.$_GET['f'].'"').'\', 500);'; ?>
                </script>
                <div style="padding: 1em; text-align: center;">
                    <?php echo t('Loading the contact feed ...'); ?>
                </div>
                <?php
            } else {
                $html = '';
                
                foreach(array_slice($messages, 0, 20) as $message) {
                    $html .= preparePost($message);
                }
                echo $html;
            }

            ?>
            <br />
            <div class="config_button" onclick="<?php $this->callAjax('ajaxWall', "'".$_GET['f']."'");?>"></div>
       	</div>
		<?php
	}
}

?>
