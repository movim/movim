<?php

class Feed extends WidgetCommon {
	function WidgetLoad()
	{
    	$this->addcss('feed.css');
    	$this->addjs('feed.js');
		$this->registerEvent('post', 'onPost');
		$this->registerEvent('comment', 'onComment');
		$this->registerEvent('nocomment', 'onNoComment');
		$this->registerEvent('nocommentstream', 'onNoCommentStream');
		$this->registerEvent('stream', 'onStream');
    }
    
    function onPost($id) {
        $query = Post::query()
                            ->where(array('key' => $this->user->getLogin(), 'nodeid' => $id));
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
            
			if(sizeof($messages) > 0 && $html != '')
				$html .= '<div class="post older" onclick="'.$this->genCallAjax('ajaxGetFeed', "'".$next."'").'; this.style.display = \'none\'">'.t('Get older posts').'</div>';
		}
		
		return $html;
	}
	
	function ajaxGetFeed($start) {
		RPC::call('movim_append', 'feedcontent', RPC::cdata($this->prepareFeed($start)));
        RPC::commit();
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
        if($content != '')
            $this->xmpp->publishItem(rawurldecode($content));
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
		$conf_arr = UserConf::getConf();
    ?>
    <div class="tabelem" title="<?php echo t('Feed'); ?>" id="feed">
		<?php
		
		if($conf_arr["first"] == 0) { 
		?>
			<script type="text/javascript">
				setTimeout('<?php $this->callAjax('ajaxCreateNode'); ?>' , 500);
			</script>

		<?php
		}
		elseif($conf_arr["first"] == 3) {
		?>
			<div class="warning" style="margin: 1.5em;">
			<?php echo t("Your server doesn't support post publication, you can only read contact's feeds"); ?>
			</div>
		<?php
		}
		else {		
		?>
		<table id="submit">
			<tr id="feedmessage">
				<td>
					<textarea 
						id="feedmessagecontent"
						onfocus="
                            if(this.value == '<?php echo t('What\\\'s new ?'); ?>') {this.value='';}
                            document.querySelector('#feedsubmitrow').style.display = 'block';" 
                        
                    ><?php echo t('What\'s new ?'); ?></textarea>
				</td>
            </tr>
            <tr id="feedsubmitrow">
                <td style="width: %"></td>
                <td>
                    <a 
                        title="<?php echo t("Submit"); ?>"
                        onclick="
                            if(
                                document.querySelector('#feedmessagecontent').value != '' && 
                                document.querySelector('#feedmessagecontent').value != '<?php echo t('What\'s new ?'); ?>') {
                                    <?php $this->callAjax('ajaxPublishItem', 'getFeedMessage()') ?>
                            }
                            else { document.querySelector('#feedmessagecontent').value ='<?php echo t('What\'s new ?'); ?>' }                  
                            "
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
		/*			<a 
                    onclick="<?php $this->callAjax('ajaxCreateNode') ?>"
                    href="#" class="button tiny icon add">&nbsp;&nbsp;<?php echo t("Create the feed"); ?></a><br />*/

        }
        
		echo $this->prepareFeed(0);
            
		?>
        </div>
    </div>
    <?php
    }
}
