<?php

use Moxl\Xec\Action\Storage\Set;
use Moxl\Xec\Action\Microblog\CommentCreateNode;
use Moxl\Xec\Action\Microblog\CreateNode;

class Feed extends WidgetCommon {
    private $_feedsize = 10;
    
    function load()
    {
        $this->addcss('feed.css');
        
        $this->registerEvent('postmicroblog', 'onStream');
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

    function display()
    {
        $this->view->assign('blog_url', Route::urlize('blog', array($this->user->getLogin(), 'urn:xmpp:microblog:0')));
        $this->view->assign('feed_url', Route::urlize('feed',array($this->user->getLogin(), 'urn:xmpp:microblog:0')));
        $this->view->assign('friend_url', Route::urlize('friend',$this->user->getLogin()));
        
        $this->view->assign('feeds', $this->prepareFeed(-1));
    }
    
    function onConfig(array $data)
    {
        $this->user->setConfig($data);
        RPC::call('movim_fill', 'feedhead', $this->prepareHead());
    }
    
    function onNodeCreated() {
        $config = $this->user->getConfig();
        $config['feed'] = 'created';
        
        $s = new Set;
        $s->setXmlns('movim:prefs')
          ->setData(serialize($config))
          ->request();
    }
    
    function onNodeCreationError() {
        $config = $this->user->getConfig();
        $config['feed'] = 'error';
        
        $s = new Set;
        $s->setXmlns('movim:prefs')
          ->setData(serialize($config))
          ->request();
        
        Notification::appendNotification(
            $this->__('feed.no_support'), 
            'error');
    }
    
    function onCommentPublishError() {
        $html =
            '<div class="message error">'.
                __('post.comment_error').'
             </div>';
        RPC::call('movim_fill', 'feednotifs', $html);
    }

    function onPostPublished($post) {        
        $html = $this->prepareFeeds();

        RPC::call('createCommentNode', $post->nodeid);   
        RPC::call('movim_fill', 'feedcontent', $html);
        RPC::call('createTabs');
    }  
    
    function ajaxCreateCommentNode($parentid) {
        $n = new CommentCreateNode;
        $n->setTo($this->user->getLogin())
          ->setParentId($parentid)
          ->request();
    }
    
    function onPostPublishError($error) {
        Notification::appendNotification($this->__('feed.error').$error, 'error');
    }
    
    function prepareHead() {
        $html = '';
        
        $session = \Sessionx::start();

        if($session->config['config'] == false) {
            $html .= 
                '<div class="message warning">'.
                    $this->__('feed.no_support').
                '</div>';
        } elseif(!isset($session->config['feed'])) {
            $html .= '
                <div id="feednotifs">
                    <div class="message info">'.
                     $this->__('feed.creating').
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
    
    function prepareNext($start, $html = '', $posts, $function = 'ajaxGetFeed') {
         // We ask for the HTML of all the posts
        $next = $start + $this->_feedsize;
        
        $nexthtml = '';
            
        if(sizeof($posts) > $this->_feedsize-1 && $html != '') {
            $nexthtml = '
                <div class="block large">
                    <div 
                        class="older" 
                        onclick="'.$this->genCallAjax($function, "'".$next."'").'; this.parentNode.style.display = \'none\'">
                        <i class="fa fa-history"></i> '. __('post.older').'
                    </div>
                </div>';
        } else {
            return '';
        }
        
        return $nexthtml;
    }
    
    function prepareFeed($start) {
        $pd = new \Modl\PostnDAO();
        $pl = $pd->getFeed($start+1, $this->_feedsize);

        if(isset($pl)) {
            $html = $this->preparePosts($pl);
            $html .= $this->prepareNext($start, $html, $pl, 'ajaxGetFeed');
        } else {
            $view = $this->tpl();
            $html = $view->draw('_feed_empty', true);
        }
        
        return $html;
    }

    function ajaxGetFeed($start) {
        $html = $this->prepareFeed($start);        
        RPC::call('movim_append', 'feedposts', $html);
        RPC::commit();
    }

    function onStream($payload) {
        $html = $this->prepareFeed(-1);
        
        if($html == '') 
            $html = '
                <div class="message info" style="margin: 1.5em; margin-top: 0em;">'.
                    __("post.no_load").'
                </div>';

        RPC::call('movim_fill', 'feedcontent', $html);
        RPC::commit();
    }
    
    function ajaxCreateNode()
    {
        $p = new CreateNode;
        $p->setTo($this->user->getLogin())
          ->request();
    }
}
