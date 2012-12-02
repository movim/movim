<?php

class Feed extends WidgetCommon {
    function WidgetLoad()
    {
        $this->addcss('feed.css');
        $this->addjs('feed.js');
        $this->registerEvent('post', 'onStream');
        $this->registerEvent('postdeleted', 'onPostDelete');
        $this->registerEvent('postdeleteerror', 'onPostDeleteError');
        
        $this->registerEvent('comment', 'onComment');
        $this->registerEvent('nocomment', 'onNoComment');
        $this->registerEvent('nocommentstream', 'onNoCommentStream');
        
		$this->registerEvent('stream', 'onStream');
        $this->registerEvent('postpublished', 'onPostPublished');
        $this->registerEvent('postpublisherror', 'onPostPublishError');
        
        $this->registerEvent('nodecreated', 'onNodeCreated');
        
        $this->registerEvent('config', 'onConfig');

        $this->cached = false;
    }
    
    function onConfig(array $data)
    {
        $this->user->setConfig($data);
        RPC::call('movim_fill', 'feedhead', RPC::cdata($this->prepareHead()));
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
    }

    function onPostPublished($post) {        
        $query = Post::query()
                            ->join('Contact', array('Post.jid' => 'Contact.jid'))
                            ->where(
                                array(
                                    'Post`.`nodeid' => $post->nodeid->getval()))
                            ->limit(0, 1);
        $messages = Post::run_query($query);

        // We ask for the HTML of all the posts
        $html = $this->preparePosts($messages);

        RPC::call('createCommentNode', $post->nodeid->getval());            
        RPC::call('movim_prepend', 'feedcontent', RPC::cdata($html));
    }  
    
    function ajaxCreateCommentNode($parentid) {
        $n = new moxl\MicroblogCommentCreateNode();
        $n->setTo($this->user->getLogin())
          ->setParentId($parentid)
          ->request();
    }
    
    function onPostPublishError($error) {
        $html .=
            '<div class="message error">'.t('An error occured : ').$error.'</div>';
        RPC::call('movim_fill', 'feednotifs', RPC::cdata($html));
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
                <table id="feedsubmitform">
                    <tbody>
                        <tr>
                            <td>
                                <textarea 
                                    placeholder="'.t("What's new ?").'" 
                                    id="feedmessagecontent" 
                                    class="steditor"
                                    onkeyup="movim_textarea_autoheight(this);"></textarea>
                            </td>
                        </tr>
                        
                        <script type="text/javascript">
                            var ste = new SimpleTextEditor("feedmessagecontent", "ste");
                            ste.init();
                        </script>
                        
                        <tr id="feedsubmitrow">
                            <td>
                                <a 
                                    title="Plus"
                                    href="#" 
                                    onclick="frameHeight(this);"
                                    style="float: left;"
                                    class="button tiny icon add merged left">'.t("Size").'
                                </a>
                                <a 
                                    title="Rich"
                                    href="#" 
                                    onclick="richText(this);"
                                    style="float: left;"
                                    class="button tiny icon yes merged right">'.t("Rich Text").'
                                </a>
                                <a 
                                    title="'.t("Submit").'"
                                    href="#" 
                                    id="feedmessagesubmit" 
                                    onclick="ste.submit();'.$this->genCallAjax('ajaxPublishItem', 'getFeedMessage()').'; ste.clearContent();"
                                    class="button tiny icon submit">'.t("Submit").'
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>



                <div id="feednotifs"></div>';
        }
        
        return $html;
    }
    
    function prepareFeed($start) {
        // We query the last messages
        $query = Post::query()
                            ->join('Contact', array('Post.jid' => 'Contact.jid'))
                            ->where(
                                array(
                                    'Post`.`parentid' => '',
                                    'Post`.`key' => $this->user->getLogin()))
                            ->orderby('Post.updated', true)
                            ->limit($start, '20');
        $messages = Post::run_query($query);

        // We ask for the HTML of all the posts
        $html = $this->preparePosts($messages);
        
        $next = $start + 20;
            
        if(sizeof($messages) > 9 && $html != '') {
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
        RPC::call('movim_append', 'feedcontent', RPC::cdata($this->prepareFeed($start)));
        RPC::commit();
    }
        
    function onStream($payload) {
        $html = '';
        $html = $this->prepareFeed(0);
        
        if($html == '') 
            $html = t("Your feed cannot be loaded.");
        RPC::call('movim_fill', 'feedcontent', RPC::cdata($html));
        RPC::commit();
    }
    
    function ajaxPublishItem($content)
    {
        if($content != '') {
            $id = md5(openssl_random_pseudo_bytes(5));
            
            $p = new moxl\MicroblogPostPublish();
            $p->setTo($this->user->getLogin())
              ->setId($id)
              ->setContent(htmlspecialchars(rawurldecode($content)))
              ->request();
        }
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
    <div class="tabelem" title="<?php echo t('Feed'); ?>" id="feed">
        <div id="feedhead">
        <?php
            echo $this->prepareHead();
        ?>
        </div>
        
        <div class="posthead">
            <ul class="filters">
                <li class="on" onclick="showPosts(this, false);"><?php echo t('All');?></li>
                <li onclick="showPosts(this, true);"><?php echo t('My Posts');?></li>
            </ul>
        </div>
        
        <div id="feedcontent">
        <?php
            echo $this->prepareFeed(0);
        ?>
        </div>
    </div>
    <?php
    }
}
