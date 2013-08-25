<?php
if (!defined('DOCUMENT_ROOT')) die('Access denied');

class News extends WidgetCommon {
    private $_feedsize = 20;
    
    function WidgetLoad()
    {
        //$this->addcss('feed.css');
        
        $this->registerEvent('post', 'onStream');
        //$this->registerEvent('postdeleted', 'onPostDelete');
        //$this->registerEvent('postdeleteerror', 'onPostDeleteError');
        
        //$this->registerEvent('comment', 'onComment');
        //$this->registerEvent('nocomment', 'onNoComment');
        //$this->registerEvent('nocommentstream', 'onNoCommentStream');
        //$this->registerEvent('commentpublisherror', 'onCommentPublishError');
        
        $this->registerEvent('stream', 'onStream');
        //$this->registerEvent('postpublished', 'onPostPublished');
        //$this->registerEvent('postpublisherror', 'onPostPublishError');
        
        //$this->registerEvent('nodecreated', 'onNodeCreated');
        //$this->registerEvent('nodecreationerror', 'onNodeCreationError');
        
        //$this->registerEvent('config', 'onConfig');
        
        //$this->view->assign('blog_url', Route::urlize('blog', array($this->user->getLogin(), false)));
        //$this->view->assign('feed_url', Route::urlize('feed',array($this->user->getLogin(), false)));
        //$this->view->assign('friend_url', Route::urlize('friend',$this->user->getLogin()));
        
        $this->view->assign('news', $this->prepareNews(-1));
    }
    /**
     * @todo nexthtml not always set... Add comments...
     */
    function prepareNext($start, $html = '', $posts, $function = 'ajaxGetFeed') {
         // We ask for the HTML of all the posts
        
        $next = $start + $this->_feedsize;
            
        if(sizeof($posts) > $this->_feedsize-1 && $html != '') {
            $nexthtml = '
                <div class="post">
                    <div 
                        class="older" 
                        onclick="'.$this->genCallAjax($function, "'".$next."'").'; this.parentNode.style.display = \'none\'">'.
                            t('Get older posts').'
                    </div>
                </div>';
        }   

        return $nexthtml;
    }
    
    function prepareNews($start) {
        $pd = new \modl\PostnDAO();
        $pl = $pd->getNews($start+1, $this->_feedsize);

        $html = $this->preparePosts($pl);

        $html .= $this->prepareNext($start, $html, $pl, 'ajaxGetNews');
        
        return $html;
    }

    function ajaxGetNews($start) {
        $html = $this->prepareNews($start);        
        RPC::call('movim_append', 'newsposts', $html);
        RPC::commit();
    }
        
    function onStream($payload) {
        $html = $this->prepareNews(-1);
        
        if($html == '') 
            $html = '
                <div class="message info" style="margin: 1.5em; margin-top: 0em;">'.
                    t("Your feed cannot be loaded.").'
                </div>';

        RPC::call('movim_fill', 'newsposts', $html);

        RPC::commit();
    }
}
