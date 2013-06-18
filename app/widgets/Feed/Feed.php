<?php

class Feed extends WidgetCommon {
	private $_feedsize = 10;
	
    function WidgetLoad()
    {
        $this->addcss('feed.css');
        
        $this->registerEvent('post', 'onStream');
        $this->registerEvent('postdeleted', 'onPostDelete');
        $this->registerEvent('postdeleteerror', 'onPostDeleteError');
        
        $this->registerEvent('comment', 'onComment');
        $this->registerEvent('nocomment', 'onNoComment');
        $this->registerEvent('nocommentstream', 'onNoCommentStream');
        $this->registerEvent('commentpublisherror', 'onCommentPublishError');
        
		$this->registerEvent('stream', 'onStream');
        $this->registerEvent('postpublished', 'onPostPublished');
        $this->registerEvent('postpublisherror', 'onPostPublishError');
        
        $this->registerEvent('nodecreated', 'onNodeCreated');
        $this->registerEvent('nodecreationerror', 'onNodeCreationError');
        
        $this->registerEvent('config', 'onConfig');
    }
    
    function onConfig(array $data)
    {
        $this->user->setConfig($data);
        RPC::call('movim_fill', 'feedhead', $this->prepareHead());
    }
    
    function onNodeCreated() {
        $config = $this->user->getConfig();
        $config['feed'] = 'created';
        
        $s = new moxl\StorageSet();
        $s->setXmlns('movim:prefs')
          ->setData(serialize($config))
          ->request();
    }
    
    function onNodeCreationError() {
        $config = $this->user->getConfig();
        $config['feed'] = 'error';
        
        $s = new moxl\StorageSet();
        $s->setXmlns('movim:prefs')
          ->setData(serialize($config))
          ->request();
        
        Notification::appendNotification(
            t("Your server doesn't support post publication, you can only read contact's feeds"), 
            'error');
    }
    
    function onCommentPublishError() {
        $html =
            '<div class="message error">'.
                t("Comment publication error").'
             </div>';
        RPC::call('movim_fill', 'feednotifs', $html);
    }

    function onPostPublished($post) {        
        $pd = new \modl\PostnDAO();
        $pl = $pd->getFeed(-1, $this->_feedsize);

        $html = $this->preparePosts($pl);

        RPC::call('createCommentNode', $post->nodeid);            
        RPC::call('movim_fill', 'feedcontent', $html);
    }  
    
    function ajaxCreateCommentNode($parentid) {
        $n = new moxl\MicroblogCommentCreateNode();
        $n->setTo($this->user->getLogin())
          ->setParentId($parentid)
          ->request();
    }
    
    function onPostPublishError($error) {
        Notification::appendNotification(t('An error occured : ').$error, 'error');
    }
    
    function prepareHead() {
        $html = '';
        
        global $session;

        if($session['config']['config'] == false) {
            $html .= 
                '<div class="message warning" style="margin: 1.5em;">'.
                    t("Your server doesn't support post publication, you can only read contact's feeds").
                '</div>';
        } elseif(!isset($session['config']['feed'])) {
            $html .= '
                <div id="feednotifs">
                    <div class="message info">'.
                     t("Creating your feed...").
                    '</div>
                </div>
                <script type="text/javascript">'.
                        $this->genCallAjax('ajaxCreateNode').
                '</script>';
        } else {
            $html .= '
                <script type="text/javascript">
                    function createCommentNode(parentid) {'.
                        $this->genCallAjax('ajaxCreateCommentNode', 'parentid[0]').
                '}
                </script>
                '.$this->prepareSubmitForm($this->user->getLogin(), 'urn:xmpp:microblog:0').'
                <div id="feednotifs"></div>';
        }
        
        return $html;
    }
    
    function prepareFeed($start) {
        $pd = new \modl\PostnDAO();
        $pl = $pd->getFeed($start+1, $this->_feedsize);

		
        $html = $this->preparePosts($pl);

        // We ask for the HTML of all the posts
        
        $next = $start + $this->_feedsize;
            
        if(sizeof($pl) > $this->_feedsize-1 && $html != '') {
            $html .= '
                <div class="post">
                    <div 
                        class="older" 
                        onclick="'.$this->genCallAjax('ajaxGetFeed', "'".$next."'").'; this.parentNode.style.display = \'none\'">'.
                            t('Get older posts').'
                    </div>
                </div>';
        }
        
        return $html;
    }

    function ajaxGetFeed($start) {
        $html = $this->prepareFeed($start);        
        RPC::call('movim_append', 'feedcontent', $html);
        RPC::commit();
    }
        
    function onStream($payload) {
        $html = '';
        $html = $this->prepareFeed(-1);
        
        if($html == '') 
            $html = '
                <div class="message info" style="margin: 1.5em; margin-top: 0em;">'.
                    t("Your feed cannot be loaded.").'
                </div>';
        RPC::call('movim_fill', 'feedcontent', $html);

        RPC::commit();
    }
    
    function ajaxCreateNode()
    {
        $p = new moxl\MicroblogCreateNode();
        $p->setTo($this->user->getLogin())
          ->request();
    }

    function build()
    { 
    ?>
    <div id="feed">
        <div id="feedhead">
        <?php
            echo $this->prepareHead();
        ?>
        </div>
        
        <div class="posthead">
            <a 
                class="button color merged left icon blog" 
                href="<?php echo Route::urlize('blog',$this->user->getLogin()); ?>"
                target="_blank">
                <?php echo t('Blog'); ?>
            </a><a 
                class="button color orange merged right icon feed" 
                href="<?php echo Route::urlize('feed',$this->user->getLogin()); ?>"
                target="_blank">
                <?php echo t('Feed'); ?> (Atom)
            </a>
            <!--<ul class="filters">
                <li class="on" onclick="showPosts(this, false);"><?php echo t('All');?></li>
                <li onclick="showPosts(this, true);"><?php echo t('My Posts');?></li>
            </ul>-->
            
            <a 
                class="button color purple icon user oppose" 
                href="<?php echo Route::urlize('friend',$this->user->getLogin()); ?>">
                <?php echo t('My Posts');?>
            </a>
        </div>
        
        <div id="feedcontent">
        <?php
            echo $this->prepareFeed(-1);
        ?>
        </div>
    </div>
    <?php
    }
}
