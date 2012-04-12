<?php

class Feed extends WidgetBase {
	function WidgetLoad()
	{
    	$this->addcss('feed.css');
    	$this->addjs('feed.js');
		$this->registerEvent('post', 'onPost');
		$this->registerEvent('comment', 'onComment');
		$this->registerEvent('stream', 'onStream');
    }
    
    function onComment($parent) {        
        global $sdb;
        $message = $sdb->select('Post', array('key' => $this->user->getLogin(), 'nodeid' => $parent));

        $html = $this->prepareComments($message[0]);
        RPC::call('movim_fill', $parent.'comments', RPC::cdata($html));
    }
    
    function onPost($payload) {
        $query = Post::query()
                            ->where(array('key' => $this->user->getLogin(), 'nodeid' => $payload['event']['items']['item']['@attributes']['id']));
        $post = Post::run_query($query);

        if($post != false) {  
            $html = $this->preparePost($post[0]);
            RPC::call('movim_prepend', 'feedcontent', RPC::cdata($html));
        }
    }
    
    function prepareFeed($start) {
        $query = Post::query()
                            ->where(array('key' => $this->user->getLogin(), 'parentid' => ''))
                            ->orderby('updated', true)
                            ->limit($start, '20');
        $messages = Post::run_query($query);
		
		if($messages == false) {
			$html = '
				<script type="text/javascript">
					setTimeout(\''.$this->genCallAjax('ajaxFeed').'\', 500);
				</script>';
			$html .=  '<div style="padding: 1em; text-align: center;">'.t('Loading your feed ...').'</div>';
		} else {
			$html = '';
			
			foreach($messages as $message) {
				$html .= $this->preparePost($message);
			}
			
            $next = $start + 20;
            
			if(sizeof($messages) > 0)
				$html .= '<div class="post older" onclick="'.$this->genCallAjax('ajaxGetFeed', "'".$next."'").'; this.style.display = \'none\'">'.t('Get older posts').'</div>';
		}
		
		return $html;
	}
	
	function ajaxGetFeed($start) {
		RPC::call('movim_append', 'feedcontent', RPC::cdata($this->prepareFeed($start)));
        RPC::commit();
	}
    
	function ajaxGetComments($jid, $id) {
		$this->xmpp->getComments($jid, $id);
	}
        
    function onStream($payload) {
        $html = '';
        $i = 0;
        
        $html = $this->prepareFeed(0);
        
        if($html == '') 
            $html = t("Your feed cannot be loaded.");
        RPC::call('movim_fill', 'feed_content', RPC::cdata($html));
        RPC::commit();
    }
    
    function ajaxPublishItem($content)
    {
        $this->xmpp->publishItem($content);
    }
    
    function ajaxCreateNode()
    {
        global $sdb;
        $conf = new ConfVar();
        $sdb->load($conf, array(
                            'login' => $this->user->getLogin()
                                ));
        $conf->set('first', true);
        $sdb->save($conf);
        
        $this->xmpp->createNode();
        
        RPC::call('movim_reload');
        RPC::commit();
    }
    
    function ajaxFeed()
    {
        $this->xmpp->getWall($this->xmpp->getCleanJid());
    }
    
    function build()
    {
    ?>
    <div class="tabelem protect orange" title="<?php echo t('Feed'); ?>" id="feed">
		<table id="submit">
			<tr>
				<td id="feedmessage">
					<input 
						id="feedmessagecontent"
						onfocus="this.value=''; this.style.color='#333333'; this.onfocus=null;" 
						value="<?php echo t('What\'s new ?'); ?>">
				</td>
				<td>
					<a 
						title="<?php echo t("Submit"); ?>"
						onclick="<?php $this->callAjax('ajaxPublishItem', "document.querySelector('#feedmessagecontent').value") ?>"
						href="#" 
						id="feedmessagesubmit" 
						class="button tiny icon submit"><?php echo t("Submit"); ?>
					</a>
				</td>
			</tr>
		</table>

        <!--<a href="#"  onclick="<?php $this->callAjax('ajaxPublishItem', "'BAZINGA !'") ?>">go !</a>-->
        <!--<a href="#"  onclick="<?php $this->callAjax('ajaxCreateNode') ?>">create !</a>-->
        <!--<a href="#"  onclick="<?php $this->callAjax('ajaxGetElements') ?>">get !</a>-->
        <div id="feedfilters">
			<ul>
				<li class="on" onclick="showPosts(this, false);"><?php echo t('All');?></li>
				<li onclick="showPosts(this, true);"><?php echo t('My Posts');?></li>
			</ul>
        </div>
        
        <div id="feedcontent">
            <?php
            
            $conf_arr = UserConf::getConf();

            if($conf_arr["first"] == 0) { 
            ?>
                    <a 
                    onclick="<?php $this->callAjax('ajaxCreateNode') ?>"
                    href="#" class="button tiny icon add">&nbsp;&nbsp;<?php echo t("Create the feed"); ?></a><br />
            <?php
            }
            
            echo $this->prepareFeed(0);
            
            ?>
        </div>
    </div>
    <?php
    }
}
