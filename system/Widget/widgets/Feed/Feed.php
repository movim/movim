<?php

class Feed extends WidgetBase {
	function WidgetLoad()
	{
    	$this->addcss('feed.css');
    	//$this->addjs('feed.js');
		$this->registerEvent('streamreceived', 'onStream');
    }
    
    function onStream($payload) {
        $html = '';
        $i = 0;
        $user = new User();
        $jid = $user->getLogin();
        
        global $sdb;
        $contact = $sdb->select('Contact', array('key' => $user->getLogin(), 'jid' => $user->getLogin()));
        
        if(isset($contact[0]))
            $photo = $contact[0]->getPhoto();
        
        if(isset($payload['pubsub']['items']['item'][0]['@attributes'])) {
            foreach($payload['pubsub']['items']['item'] as $post) {
                $html .= '
                <div class="post" id="'.$post['@attributes']['id'].'">
			        <img class="avatar" src="'.$photo.'">

     			        <span><a href="?q=friend&f='.$jid.'">'.t('Me').'</a></span>
     			        <span class="date">'.prepareDate(strtotime($post['entry']['published'])).'</span>
     			    <div class="content"> 
     			    '.prepareString($post['entry']['content']).'
	            	</div>
	            	<!--<div class="comments" id="'.$post['@attributes']['id'].'comments">
	            	    <a onclick="'.$this->genCallAjax('ajaxGetComments', "'".$_GET['f']."'", "'".$post['@attributes']['id']."'").'">'.t('Get the comments').'</a>
	            	</div>-->
           		</div>';
            }
        }
        
        if($html == '') 
            $html = t("Your feed cannot be loaded.");
        RPC::call('movim_fill', 'feed_content', RPC::cdata($html));
    }
    
    function ajaxPublishItem($content)
    {
		$xmpp = Jabber::getInstance();
        $xmpp->publishItem($content);
    }
    
    function ajaxCreateNode()
    {
		$xmpp = Jabber::getInstance();
        $xmpp->createNode();
    }
    
    function ajaxFeed()
    {
		$xmpp = Jabber::getInstance();
        $xmpp->getWall($xmpp->getCleanJid());
    }
    
    function build()
    {
    ?>
    <div class="tabelem" title="<?php echo t('Feed'); ?>" id="feed">
        <textarea id="feedmessage" onfocus="this.value=''; this.style.color='#333333'; this.onfocus=null;"><?php echo t('What\'s new ?'); ?></textarea>
        <a 
            onclick="<?php $this->callAjax('ajaxPublishItem', "document.querySelector('#feedmessage').value") ?>"
            href="#" id="feedmessagesubmit" class="button tiny"><?php echo t("Submit"); ?></a><br />
        <!--<a href="#"  onclick="<?php $this->callAjax('ajaxPublishItem', "'BAZINGA !'") ?>">go !</a>-->
        <!--<a href="#"  onclick="<?php $this->callAjax('ajaxCreateNode') ?>">create !</a>-->
        <!--<a href="#"  onclick="<?php $this->callAjax('ajaxGetElements') ?>">get !</a>-->
        <div id="feed_content">
            <script type="text/javascript">

            <?php 
                echo 'setTimeout(\''.$this->genCallAjax('ajaxFeed').'\', 500);'; ?>
            </script>
            <?php echo t('Loading your feed ...'); ?>
        </div>
    </div>
    <?php
    }
}
