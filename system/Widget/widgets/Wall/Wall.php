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

class Wall extends WidgetCommon
{

    function WidgetLoad()
    {
    	$this->addcss('wall.css');
    	$this->addjs('wall.js');
		$this->registerEvent('post', 'onNewPost');
		$this->registerEvent('stream', 'onStream');
		$this->registerEvent('comment', 'onComment');
		$this->registerEvent('nocomment', 'onNoComment');
		$this->registerEvent('nocommentstream', 'onNoCommentStream');
        $this->registerEvent('nostream', 'onNoStream');
        $this->registerEvent('nostreamautorized', 'onNoStreamAutorized');
    }
    
    function onNewPost($id) {
        $query = Post::query()
                            ->where(array('key' => $this->user->getLogin(), 'nodeid' => $id));
        $post = Post::run_query($query);

        if($post != false) {  
            $html = $this->preparePost($post[0]);
            RPC::call('movim_prepend', 'wall', RPC::cdata($html));
        }
    }
    
    function onNoStream() {
        $html = '<div style="padding: 1.5em; text-align: center;">Ain\'t Nobody Here But Us Chickens...</div>';
        RPC::call('movim_fill', 'wall', RPC::cdata($html));
        RPC::call('hideWall');
        RPC::commit();
    }
    
    function onNoStreamAutorized() {
        $html = '<div style="padding: 1.5em; text-align: center;">I\'m sorry, Dave. I\'m afraid I can\'t do that.</div>';
        RPC::call('movim_fill', 'wall', RPC::cdata($html));
        RPC::commit();
    }  
    
    function onStream($from) {
        /*$html = '';

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
        */
        $html = $this->prepareFeed(0, $from);

        RPC::call('movim_fill', 'wall', RPC::cdata($html));
    }

    function prepareFeed($start, $from = false) {
        
        if(!$from)
            $from = $_GET['f'];
        // We query the last messages
        $query = Post::query()
                            ->join('Contact', array('Post.jid' => 'Contact.jid'))
                            ->where(array(
                                'Contact`.`key' => $this->user->getLogin(),
                                'Post`.`jid' => $from,
                                'Post`.`parentid' => ''))
                            ->orderby('Post.updated', true)
                            ->limit($start, '20');
        $messages = Post::run_query($query);
		
        // We ask for the HTML of all the posts
        $htmlmessages = $this->preparePosts($messages);
        
        $next = $start + 10;
            
        if(sizeof($messages) > 0 && $htmlmessages != false) {
            if($start == 0) {
                $html .= '
                        <div id="wallheader">
                        <a 
                                    class="button tiny icon follow" 
                                    href="#"
                                    onclick="'.$this->genCallAjax('ajaxWall', "'".$from."'").'
                                        this.innerHTML = \''.t('Updating').'\'; 
                                        this.className= \'button tiny icon loading\';
                                        this.onclick = \'return false;\'";
                                >
                                    '.t('Update').'
                                </a>
                        </div>';
            }
            $html .= $htmlmessages;
            if(sizeof($messages) > 9)
                $html .= '<div class="post older" onclick="'.$this->genCallAjax('ajaxGetFeed', "'".$next."'", "'".$from."'").'; this.style.display = \'none\'">'.t('Get older posts').'</div>';
		}
        
		return $html;
	}
    
	function ajaxGetFeed($start, $from) {
		RPC::call('movim_append', 'wall', RPC::cdata($this->prepareFeed($start, $from)));
        RPC::commit();
	}

	function ajaxWall($jid) {
        $r = new moxl\MicroblogGet();
        $r->setTo($jid)->request();
	}
	
	function ajaxSubscribe($jid) {
		$this->xmpp->subscribeNode($jid);
	}

	function build()
	{
		?>
		<div class="tabelem protect orange" id="wall" title="<?php echo t('Feed');?>">
		<?php 
            $wall = $this->prepareFeed(0);
            if($wall)
                echo $wall;
            else {
            ?>
                <div style="padding: 1.5em; text-align: center;">Ain't Nobody Here But Us Chickens...</div>
                <script type="text/javascript">
                    <?php echo 'setTimeout(\''.$this->genCallAjax('ajaxWall', '"'.$_GET['f'].'"').'\', 500);'; ?>
                </script>
            <?php
            }
        /*        <!--<a 
                    class="button tiny icon follow" 
                    href="#"
                    style="float: right;"
                    onclick="<?php echo $this->callAjax('ajaxSubscribe', "'".$_GET['f']."'"); ?>" 
                >
                    <?php echo t('Follow'); ?>
                </a>
                <br /><br />-->
            <?php 
            $query = Post::query()
                                ->where(array(
                                            'key' => $this->user->getLogin(), 
                                            'parentid' => '',
                                            'jid' => $_GET['f']))
                                ->orderby('updated', true)
                                ->limit('0', '20');
            $messages = Post::run_query($query);
            
            if($messages == false) {
            ?>
                <script type="text/javascript">
                <?php echo 'setTimeout(\''.$this->genCallAjax('ajaxWall', '"'.$_GET['f'].'"').'\', 500);'; ?>
                </script>
                <div style="padding: 1.5em; text-align: center;">
                    <?php echo t('Loading the contact feed ...'); ?>
                </div>
                <?php
            } else {
                $html = '';
                
                foreach($messages as $message) {
                    $html .= $this->preparePost($message);
                }
                echo $html;
            }

            ?>
            <br />
            <div class="config_button" onclick="<?php $this->callAjax('ajaxWall', "'".$_GET['f']."'");?>"></div>*/ ?>
       	</div>
		<?php
	}
}

?>
