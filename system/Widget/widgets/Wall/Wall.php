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
		$this->registerEvent('streamreceived', 'onStream');
		$this->registerEvent('comments', 'onComments');
		$this->registerEvent('currentpost', 'onNewPost');
    }
    
    function preparePost($post, $user) {
        if($post['entry']['content'] != "") {
            global $sdb;
            
            $jid = substr_replace($post['entry']['source']['author']['uri'], "", 0, 5);
            
            $contact = $sdb->select('Contact', array('key' => $user->getLogin(), 'jid' => $jid));
            if(isset($contact[0])) {
                $photo = $contact[0]->getPhoto();
                $name = $contact[0]->getTrueName();
            }
            
            $html = '
                <div class="post" id="'.$post['@attributes']['id'].'">
			        <img class="avatar" src="'.$photo.'">

     			        <span><a href="?q=friend&f='.$jid.'">'.$name.'</a></span>
     			        <span class="date">'.prepareDate(strtotime($post['entry']['published'])).'</span>
     			    <div class="content"> 
     			    '.prepareString($post['entry']['content']).'
	            	</div>
	            	<div class="comments" id="'.$post['@attributes']['id'].'comments">
	            	    <a onclick="'.$this->genCallAjax('ajaxGetComments', "'".$_GET['f']."'", "'".$post['@attributes']['id']."'").'">'.t('Get the comments').'</a>
	            	</div>
           		</div>';
            return $html;
        } else { 
            return "";
        }
    }
    
    function onNewPost($payload) {
         $user = new User(); 
         $html = $this->preparePost($payload['event']['items']['item'], $user);
         
        RPC::call('movim_prepend', 'wall', RPC::cdata($html));
    }
    
    function onStream($payload) {
        $html = '';

        if(isset($payload['error'])) 
            $html = t("Contact's feed cannot be loaded.");
        else {
            $html .= '
                <!--<a 
                    class="button tiny icon" 
                    href="#"
                    style="float: right;"
                    id="wallfollow" 
                    onclick="'.$this->genCallAjax('ajaxSubscribe', "'".$payload["movim"]["@attributes"]["from"]."'").'" 
                >
                    '.t('Follow').'
                </a>
                <br /><br />-->
                ';
            
            $user = new User();
            
            if(isset($payload['pubsub']['items']['item'][0]['@attributes'])) {
                foreach($payload['pubsub']['items']['item'] as $item)
                    $html .= $this->preparePost($item, $user);
            } else {
                $html .= $this->preparePost($payload['pubsub']['items']['item'], $user);
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
                $photo = $contact[0]->getPhoto();
                $name = $contact[0]->getTrueName();
            }
            
            $html .= '
                <div class="comment">
                	<img class="avatar tiny" src="'.$photo.'">
                    <span>'.$payload['movim']['pubsub']['items']['item']['entry']['source']['author']['name'].'</span>
                    <span class="date">'.prepareDate(strtotime($payload['movim']['pubsub']['items']['item']['entry']['published'])).'</span><br />
                    <div class="content tiny">'.prepareString($payload['movim']['pubsub']['items']['item']['entry']['content']).'</div>
                </div>';
        } elseif(isset($payload['movim']['pubsub']['items']['item'])) {
            foreach($payload['movim']['pubsub']['items']['item'] as $comment) {
            
                $jid = substr_replace($comment['entry']['source']['author']['uri'], "", 0, 5);
                $contact = $sdb->select('Contact', array('key' => $user->getLogin(), 'jid' => $jid));
                
                if(isset($contact[0])) {
                    $photo = $contact[0]->getPhoto();
                    $name = $contact[0]->getTrueName();
                }
            
                $html = '
                    <div class="comment">
                    	<img class="avatar tiny" src="'.$photo.'">
                        <span>'.$comment['entry']['source']['author']['name'].'</span>
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
