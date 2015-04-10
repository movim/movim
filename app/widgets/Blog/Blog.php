<?php

class Blog extends WidgetCommon {
    function load()
    {
        
    }

    function display()
    {
        if(!$this->get('f')) {
            return;
        }
        
        $from = $this->get('f');
        if(filter_var($from, FILTER_VALIDATE_EMAIL)) {
            $node = 'urn:xmpp:microblog:0';
        } else {
            return;
        }

        $cd = new \modl\ContactDAO();
        $c  = $cd->get($from, true);
        $this->view->assign('contact', $c);
        
        $pd = new \modl\PostnDAO();
        $messages = $pd->getPublic($from, $node, 10, 0);
        
        $this->view->assign('posts', $messages);
    }
}
