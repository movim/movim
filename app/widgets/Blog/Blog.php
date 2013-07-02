<?php

class Blog extends WidgetCommon {
    function WidgetLoad()
    {
        $from = $_GET['f'];
        $node = $_GET['n'];
        
        $this->view->assign('from', $from);
        $this->view->assign('node', $node);
        
        $pd = new \modl\PostnDAO();
        $messages = $pd->getPublic($from, $node);
        
        if(isset($messages[0])) {
            // Title and logo
            // For a Pubsub feed
            if(isset($from) && isset($node) && $node != '') {
                $pd = new \modl\NodeDAO();
                $n = $pd->getNode($from, $node);
                if(isset($n->title))
                    $this->view->assign('title', $n->title);
                else
                    $this->view->assign('title', $n->nodeid);
            // Fir a simple contact
            } else {
                $this->view->assign('title', t("%s's feed",$messages[0]->getContact()->getTrueName()));
                $this->view->assign('logo', $messages[0]->getContact()->getPhoto('l'));
            }
            
            $this->view->assign('date', date('c'));
            $this->view->assign('name', $messages[0]->getContact()->getTrueName());
            $this->view->assign('feed', Route::urlize('feed',array($from, $node)));
        }
        
        $this->view->assign('posts', $this->preparePosts($messages, true));
    }
}
