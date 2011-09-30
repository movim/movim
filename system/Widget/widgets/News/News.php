<?php

class News extends WidgetBase {
	function WidgetLoad()
	{
    	$this->addcss('news.css');
    }

    function build()
    {
    ?>
    <div class="tabelem" title="<?php echo t('News'); ?>" id="news">
    <?php
        global $sdb;
        $user = new User();
        $messages = $sdb->select('Message', array('key' => $user->getLogin()));
        
        foreach($messages as $message) {
            if($user->getLogin() != $message->getData('jid')) {
                $contact = $sdb->select('Contact', array('key' => $user->getLogin(), 'jid' => $message->getData('jid')));
                $html .= '
                    <div class="post" id="'.$message->getData('nodeid').'">
				        <img class="avatar">

		            	<div class="content">
         			        <span><a href="?q=friend&f='.$message->getData('jid').'">'.$contact[0]->getTrueName().'</a></span> <span class="date">'.date('j F - H:i',strtotime($message->getData('updated'))).'</span> '.$message->getData('content').'
		            	</div>
               		</div>';
           }
        }
        echo $html;
    ?>
    </div>
    <?php
    }
}
