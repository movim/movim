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
        }
        
        $pd = new \modl\PostnDAO();
        $messages = $pd->getPublic($from, $node);

        if($messages[0] != null) {
            // Title and logo

            $cd = new \modl\ContactDAO();
            $c  = $cd->get($from);
            $this->view->assign('contact', $c);
        } else {
            $this->view->assign('title', $this->__('page.feed'));
        }
        
        $this->view->assign('posts', $messages);
    }
}
