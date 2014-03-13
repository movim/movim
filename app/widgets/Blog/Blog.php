<?php

class Blog extends WidgetCommon {
    function load()
    {
        $from = $_GET['f'];
        $node = $_GET['n'];
        
        $this->view->assign('from', $from);
        if(isset($node))
            $this->view->assign('node', $node);
        
        $pd = new \modl\PostnDAO();
        
        if(isset($from) && isset($node))
            $messages = $pd->getPublic($from, $node);

        if($messages[0] != null) {
            // Title and logo
            // For a Pubsub feed
            if(isset($from) && isset($node) && $node != 'urn:xmpp:microblog:0') {
                $pd = new \modl\NodeDAO();
                $n = $pd->getNode($from, $node);
                if(isset($n->title))
                    $this->view->assign('title', $n->title);
                elseif(isset($n->nodeid))
                    $this->view->assign('title', $n->nodeid);
            // For a simple contact
            } else {
                $this->view->assign('title', t("%s's feed",$messages[0]->getContact()->getTrueName()));
                $this->view->assign('logo', $messages[0]->getContact()->getPhoto('l'));
            }
            
            $this->view->assign('date', date('c'));
            $this->view->assign('name', $messages[0]->getContact()->getTrueName());
            $this->view->assign('feed', Route::urlize('feed',array($from, $node)));
        } else {
            $this->view->assign('title', t('Feed'));
        }
        
        $this->view->assign('posts', $this->preparePosts($messages, true));
    }
}
