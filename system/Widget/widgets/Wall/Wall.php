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
    
    function preparePost($message, $user) {
        global $sdb;
        $contact = $sdb->select('Contact', array('key' => $user->getLogin(), 'jid' => $message->getData('jid')));
        
        $tmp = '';
        
        if(isset($contact[0])) {
            $tmp = '
                <div class="post" id="'.$message->getData('nodeid').'">
		            <img class="avatar" src="'.$contact[0]->getPhoto('m').'">

     			    <span><a href="?q=friend&f='.$message->getData('jid').'">'.$contact[0]->getTrueName().'</a></span>
     			    <span class="date">'.prepareDate(strtotime($message->getData('updated'))).'</span>
     			    <div class="content">
     			        '.prepareString($message->getData('content')). '</div>';
     			        
            $attachments = AttachmentHandler::getAttachment($message->getData('nodeid'));
            if($attachments) {
                $tmp .= '<div class="attachment">';
                foreach($attachments as $attachment)
                    $tmp .= '<a target="_blank" href="'.$attachment->getData('link').'"><img src="'.$attachment->getData('thumb').'"></a>';
                $tmp .= '</div>';
            }
            
     	    if($message->getPlace() != false)
     		    $tmp .= '<span class="place">
     		                <a 
     		                    target="_blank" 
     		                    href="http://www.openstreetmap.org/?lat='.$message->getData('lat').'&lon='.$message->getData('lon').'&zoom=10"
     		                >'.$message->getPlace().'</a>
     		             </span>';
              
            $tmp .= '
	            	<div class="comments" id="'.$message->getData('nodeid').'comments">
	            	    <a class="getcomments icon bubble" style="margin-left: 0px;" onclick="'.$this->genCallAjax('ajaxGetComments', "'".$message->getData('jid')."'", "'".$message->getData('nodeid')."'").'; this.innerHTML = \''.t('Loading comments ...').'\'">'.t('Get the comments').'</a>
	            	</div>
           		</div>';

        }
       	return $tmp;
    }
    
    function onNewPost($payload) {
        global $sdb;
        $user = new User(); 
        $message = $sdb->select('Message', array('nodeid' => $payload['event']['items']['item']['@attributes']['id']));
        $html = $this->preparePost($message[0], $user);

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
            $user = new User();
            $messages = $sdb->select('Message', array('key' => $user->getLogin(), 'jid' => $payload["@attributes"]["from"]), 'updated', true);
            
            if($messages == false) {            
                RPC::call('hideWall'); 
            } else {
                $html = '';
                
                foreach(array_slice($messages, 0, 20) as $message) {
                    $html .= $this->preparePost($message, $user);
                }
                echo $html;
            }
        }

        RPC::call('movim_fill', 'wall', RPC::cdata($html));
    }
    
    function onComments($payload) {
        list($xmlns, $id) = explode("/", $payload['movim']['pubsub']['items']['@attributes']['node']);
    
        $html = '';
        global $sdb;
        $user = new User();
        
        if(isset($payload['movim']['pubsub']['items']['item']['@attributes']['id'])) {
            
            $jid = substr_replace($payload['movim']['pubsub']['items']['item']['entry']['source']['author']['uri'], "", 0, 5);
            $contact = $sdb->select('Contact', array('key' => $user->getLogin(), 'jid' => $jid));
            
            if(isset($contact[0])) {
                $photo = $contact[0]->getPhoto('s');
                $name = $contact[0]->getTrueName();
            }
            
            $html .= '
                <div class="comment">
                	<img class="avatar tiny" src="'.$photo.'">
                    <span><a href="?q=friend&f='.substr_replace($payload['movim']['pubsub']['items']['item']['entry']['source']['author']['uri'], "", 0, 5).'">'.$payload['movim']['pubsub']['items']['item']['entry']['source']['author']['name'].'</a></span>
                    <span class="date">'.prepareDate(strtotime($payload['movim']['pubsub']['items']['item']['entry']['published'])).'</span><br />
                    <div class="content tiny">'.prepareString($payload['movim']['pubsub']['items']['item']['entry']['content']).'</div>
                </div>';
        } elseif(isset($payload['movim']['pubsub']['items']['item'])) {
            foreach($payload['movim']['pubsub']['items']['item'] as $comment) {
            
                $jid = substr_replace($comment['entry']['source']['author']['uri'], "", 0, 5);
                $contact = $sdb->select('Contact', array('key' => $user->getLogin(), 'jid' => $jid));
                
                if(isset($contact[0])) {
                    $photo = $contact[0]->getPhoto('s');
                    $name = $contact[0]->getTrueName();
                }
            
                $html = '
                    <div class="comment">
                    	<img class="avatar tiny" src="'.$photo.'">
                        <span><a href="?q=friend&f='.substr_replace($comment['entry']['source']['author']['uri'], "", 0, 5).'">'.$comment['entry']['source']['author']['name'].'</a></span>
                        <span class="date">'.prepareDate(strtotime($comment['entry']['published'])).'</span><br />
                        <div class="content tiny">'.prepareString($comment['entry']['content']).'</div>
                    </div>' . $html;
            }
        }
        
        RPC::call('movim_fill', $id.'comments', RPC::cdata($html));
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
            $user = new User();
            $messages = $sdb->select('Message', array('key' => $user->getLogin(), 'jid' => $_GET['f']), 'updated', true);
            
            if($messages == false) {
            ?>
                <script type="text/javascript">
                <?php echo 'setTimeout(\''.$this->genCallAjax('ajaxWall', '"'.$_GET['f'].'"').'\', 500);'; ?>
                </script>
            <?php
                echo t('Loading the contact feed ...'); 
            } else {
                $html = '';
                
                foreach(array_slice($messages, 0, 20) as $message) {
                    $html .= $this->preparePost($message, $user);
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
