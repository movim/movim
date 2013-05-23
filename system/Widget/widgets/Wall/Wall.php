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
		$this->registerEvent('post', 'onStream');
		$this->registerEvent('stream', 'onStream');
		$this->registerEvent('comment', 'onComment');
		$this->registerEvent('nocomment', 'onNoComment');
		$this->registerEvent('nocommentstream', 'onNoCommentStream');
        $this->registerEvent('nostream', 'onNoStream');
        $this->registerEvent('nostreamautorized', 'onNoStreamAutorized');
    }
    
    function onNoStream() {
        $html = '<div style="padding: 1.5em; text-align: center;">Ain\'t Nobody Here But Us Chickens...</div>';
        RPC::call('movim_fill', 'wall', $html);
        RPC::call('hideWall');
        RPC::commit();
    }
    
    function onNoStreamAutorized() {
        $html = '<div style="padding: 1.5em; text-align: center;">I\'m sorry, Dave. I\'m afraid I can\'t do that.</div>';
        RPC::call('movim_fill', 'wall', $html);
        RPC::commit();
    }  
    
    function onStream($payload) {
        $html = $this->prepareFeed(-1, $payload['from']);

        RPC::call('movim_fill', md5($payload['from'].$payload['node']), $html);
    }

    function prepareFeed($start, $from = false) {
        
        if(!$from)
            $from = $_GET['f'];
        
        $pd = new \modl\PostnDAO();
        $pl = $pd->getNode($from, 'urn:xmpp:microblog:0', $start+1, 10);
        
        // We ask for the HTML of all the posts
        
        $htmlmessages = $this->preparePosts($pl);

        $next = $start + 10;
        
        if(count($pl) > 0 && $htmlmessages != false) {
            if($start == -1) {
                $html .= '
                        <div class="posthead">
                                <a 
                                    class="button tiny icon feed merged left" 
                                    href="?q=feed&f='.$from.'"
                                    target="_blank"
                                >
                                    '.t('Feed').' (Atom)
                                </a><a 
                                    class="button tiny icon refresh merged right" 
                                    href="#"
                                    onclick="'.$this->genCallAjax('ajaxWall', "'".$from."'").'
                                        this.innerHTML = \''.t('Updating').'\'; 
                                        this.className= \'button tiny icon merged right loading\';
                                        this.onclick = \'return false;\'";
                                >
                                    '.t('Update').'
                                </a>
                        </div>';
            }
            $html .= $htmlmessages;
            if(count($pl) > 9)
                $html .= '
                    <div class="post">
                        <div class="older" onclick="'.$this->genCallAjax('ajaxGetFeed', "'".$next."'", "'".$from."'").';  this.parentNode.style.display = \'none\'">'.t('Get older posts').'</div>
                    </div>';
		}
        
		return $html;
	}
    
	function ajaxGetFeed($start, $from) {
		RPC::call('movim_append', 'wall', $this->prepareFeed($start, $from));
        RPC::commit();
	}

	function ajaxWall($jid) {
        $r = new moxl\PubsubGetItems();
        $r->setTo($jid)
          ->setNode('urn:xmpp:microblog:0')
          ->request();
	}
	
	function ajaxSubscribe($jid) {
		$this->xmpp->subscribeNode($jid);
	}

	function build()
	{
		?>
		<div class="tabelem protect orange" id="wall" title="<?php echo t('Feed');?>">
            <div id="<?php echo md5($_GET['f'].'urn:xmpp:microblog:0'); ?>">
            <?php 
                $wall = $this->prepareFeed(-1);
                if($wall)
                    echo $wall;
                else {
                ?>
                    <div style="padding: 1.5em; text-align: center;">Ain't Nobody Here But Us Chickens...</div>
                    <script type="text/javascript">
                        <?php echo 'setTimeout(\''.$this->genCallAjax('ajaxWall', '"'.$_GET['f'].'"').'\', 500);'; ?>
                    </script>
                <?php
                } ?>
            </div>
       	</div>
		<?php
	}
}

?>
