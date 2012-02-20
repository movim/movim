<?php

class News extends WidgetBase {
	function WidgetLoad()
	{
		$this->registerEvent('post', 'onPost');
    }
    
    function onPost($payload) {
        global $sdb;
        $post = $sdb->select('Message', array('key' => $this->user->getLogin(), 'nodeid' => $payload['event']['items']['item']['@attributes']['id']));

        if($post != false) {  
            $html = $this->preparePost($post[0], $user);
            RPC::call('movim_prepend', 'news', RPC::cdata($html));
        }
    }
    
    function preparePost($message) {
        global $sdb;
        $contact = $sdb->select('Contact', array('key' => $this->user->getLogin(), 'jid' => $message->getData('jid')));
        
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

    function build()
    {
    ?>
    <div class="tabelem protect orange" style="padding-top: 15px;" title="<?php echo t('News'); ?>" id="news">
    <?php
        global $sdb;
        $messages = $sdb->select('Message', array('key' => $this->user->getLogin()), 'updated', true);
        
        $html = '';
        if($messages != false) {  
            
            foreach(array_slice($messages, 0, 20) as $message) {
                if($this->user->getLogin() != $message->getData('jid')) {
                    $html .= $this->preparePost($message);
               }
            }
            echo $html;
        }
        
        if($messages == false || $html == '') {
            echo t('You have no news yet...');
        }
    ?>
    </div>
    <?php
    }
}
