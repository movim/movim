<?php

class Blog extends WidgetCommon {
    function load()
    {
        
    }

    function display()
    {
        if(!isset($_GET['f'])) {
            return;
        }
        
        $from = $_GET['f'];
        if(filter_var($from, FILTER_VALIDATE_EMAIL)) {
            $node = 'urn:xmpp:microblog:0';
        } else {
            return;
            //$node = $_GET['n'];
        }

        /*$this->view->assign('from', $from);
        if(isset($node))
            $this->view->assign('node', $node);*/
        
        $pd = new \modl\PostnDAO();
        
        //if(isset($from) && isset($node))
        $messages = $pd->getPublic($from, $node);

        if($messages[0] != null) {
            // Title and logo
            // For a Pubsub feed
            /*if(isset($from) && isset($node) && $node != 'urn:xmpp:microblog:0') {
                $pd = new \modl\NodeDAO();
                $n = $pd->getNode($from, $node);
                if(isset($n->title))
                    $this->view->assign('title', $n->title);
                elseif(isset($n->nodeid))
                    $this->view->assign('title', $n->nodeid);
            // For a simple contact
            } else {
                $this->view->assign('title', $this->__('blog.title',$messages[0]->getContact()->getTrueName()));
                $this->view->assign('logo', $messages[0]->getContact()->getPhoto('l'));
            }*/

            $cd = new \modl\ContactDAO();
            $c  = $cd->get($from);
            $this->view->assign('contact', $c);

            //$this->view->assign('date', date('c'));
            //$this->view->assign('name', $messages[0]->getContact()->getTrueName());
            //$this->view->assign('feed', Route::urlize('feed',array($from, $node)));
        } else {
            $this->view->assign('title', $this->__('page.feed'));
        }
        
        $this->view->assign('posts', $messages);
        //$this->view->assign('posts', $this->preparePosts($messages, true));
    }
}
