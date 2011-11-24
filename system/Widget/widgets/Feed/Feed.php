<?php

class Feed extends WidgetBase {
	function WidgetLoad()
	{
    	$this->addcss('feed.css');
		$this->registerEvent('post', 'onPost');
		$this->registerEvent('streamreceived', 'onStream');
    }
    
    function onPost($payload) {
        global $sdb;
        $user = new User();
        $post = $sdb->select('Message', array('key' => $user->getLogin(), 'nodeid' => $payload['event']['items']['item']['@attributes']['id']));

        if($post != false) {  
            $html = $this->preparePost($post[0], $user);
            RPC::call('movim_prepend', 'feed_content', RPC::cdata($html));
        }
    }
    
    function preparePost($message, $user) {
        global $sdb;
        $contact = $sdb->select('Contact', array('key' => $user->getLogin(), 'jid' => $message->getData('jid')));
        
        $tmp = '';
        
        if(isset($contact[0])) {
            $tmp = '
                <div class="post" id="'.$message->getData('nodeid').'">
		            <img class="avatar" src="'.$contact[0]->getPhoto('s').'">

     			    <span><a href="?q=friend&f='.$message->getData('jid').'">'.$contact[0]->getTrueName().'</a></span> 
     			    <span class="date">'.prepareDate(strtotime($message->getData('updated'))).'</span>
     			    <div class="content">
     			        '.prepareString($message->getData('content')).'
                	</div>
           		</div>';
        }
       	return $tmp;
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
    <div class="tabelem protect orange" title="<?php echo t('Feed'); ?>" id="feed">
        <textarea id="feedmessage" onfocus="this.value=''; this.style.color='#333333'; this.onfocus=null;"><?php echo t('What\'s new ?'); ?></textarea>
        <a 
            onclick="<?php $this->callAjax('ajaxPublishItem', "document.querySelector('#feedmessage').value") ?>"
            href="#" id="feedmessagesubmit" class="button tiny icon submit"><?php echo t("Submit"); ?></a><br />
        <!--<a href="#"  onclick="<?php $this->callAjax('ajaxPublishItem', "'BAZINGA !'") ?>">go !</a>-->
        <!--<a href="#"  onclick="<?php $this->callAjax('ajaxCreateNode') ?>">create !</a>-->
        <!--<a href="#"  onclick="<?php $this->callAjax('ajaxGetElements') ?>">get !</a>-->
        <div id="feed_content">
            <?php
            
            global $sdb;
            $user = new User();
            $messages = $sdb->select('Message', array('key' => $user->getLogin(), 'jid' => $user->getLogin()), 'updated', true);
            
            if($messages == false) {
            ?>
                <script type="text/javascript">
                    <?php echo 'setTimeout(\''.$this->genCallAjax('ajaxFeed').'\', 500);'; ?>
                </script>
            <?php
                echo t('Loading your feed ...');
            } else {
                $html = '';
                
                foreach(array_slice($messages, 0, 20) as $message) {
                    $html .= $this->preparePost($message, $user);
                }
                echo $html;
            }
            ?>
        </div>
    </div>
    <?php
    }
}
