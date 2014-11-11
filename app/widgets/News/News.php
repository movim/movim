<?php

class News extends WidgetCommon {
    private $_feedsize = 20;
    
    function load()
    {
        $this->registerEvent('post', 'onStream');
        $this->registerEvent('stream', 'onStream');
        $this->addcss('news.css');
    }

    function display()
    {
        $this->view->assign('news', $this->prepareNews(-1));
    }
    
    /**
     * @todo nexthtml not always set... Add comments...
     */
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
                        <i class="fa fa-history"></i> '.__('post.older').'
                    </div>
                </div>';
        }   

        return $nexthtml;
    }
    
    function prepareNews($start) {
        $pd = new \Modl\PostnDAO();
        $pl = $pd->getNews($start+1, $this->_feedsize);

        if(isset($pl)) {
            $html = $this->preparePosts($pl);
            Cache::c('since', date(DATE_ISO8601, strtotime($pd->getLastDate())));
            $html .= $this->prepareNext($start, $html, $pl, 'ajaxGetNews');
        } else {
            $view = $this->tpl();
            $html = $view->draw('_news_empty', true);
        }
        
        return $html;
    }

    function ajaxGetNews($start) {
        $html = $this->prepareNews($start);        
        RPC::call('movim_append', 'newsposts', $html);
        RPC::commit();
    }

    function ajaxRefresh() {
        $html = $this->prepareNews(-1);
        
        if($html == '') 
            $html = '
                <div class="message info" style="margin: 1.5em; margin-top: 0em;">'.
                    __('post.no_load').'
                </div>';

        RPC::call('movim_fill', 'refresh', '');
        RPC::call('movim_fill', 'newsposts', $html);
        RPC::call('movim_posts_unread', 0);

        RPC::commit();
    }
        
    function onStream($payload) {
        $pd = new \Modl\PostnDAO();
        $count = $pd->getCountSince(Cache::c('since'));

        if($count > 0) {
            $html = '
                <a class="button color green icon refresh"
                   onclick="'.$this->genCallAjax('ajaxRefresh').'"
                >'.
                    __('post.new_items', $count).' - '.__('button.refresh').'
                </a>';
                
            RPC::call('movim_posts_unread', $count);
            RPC::call('movim_fill', 'refresh', $html);
            
            RPC::commit();
        }
    }
}
