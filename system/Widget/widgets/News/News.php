<?php

class News extends WidgetBase {
	function WidgetLoad()
	{
		$this->registerEvent('post', 'onPost');
    }
    
    function onPost($payload) {
        global $sdb;
        $user = new User();
        $post = $sdb->select('Message', array('key' => $user->getLogin(), 'nodeid' => $payload['event']['items']['item']['@attributes']['id']));

        $html = $this->preparePost($post[0], $user);
        
        RPC::call('movim_prepend', 'news', RPC::cdata($html));
    }
    
    function preparePost($message, $user) {
        global $sdb;
        $contact = $sdb->select('Contact', array('key' => $user->getLogin(), 'jid' => $message->getData('jid')));
        
        $tmp = '';
        
        if(isset($contact[0])) {
            movim_log($contact);
            $tmp = '
                <div class="post" id="'.$message->getData('nodeid').'">
		            <img class="avatar" src="'.$contact[0]->getPhoto().'">

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
    <div class="tabelem" title="<?php echo t('News'); ?>" id="news">
    <?php
        global $sdb;
        $user = new User();
        $messages = $sdb->select('Message', array('key' => $user->getLogin()));
        
        $date = 1;
        $html = '';
        
        foreach($messages as $message) {
            if($user->getLogin() != $message->getData('jid')) {
                $tmp = $this->preparePost($message, $user);
                if($message->getData('updated') > $date) {
                    $html = $tmp . $html;
                    $date = $message->getData('updated');
                } else {
                    $html .= $tmp;
                }
           }
        }
        echo $html;
    ?>
    </div>
    <?php
    }
}
