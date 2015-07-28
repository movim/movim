<?php

class Blog extends WidgetBase {
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
        if($id = $this->get('i')) {
            $messages = $pd->getPublicItem($from, $node, $id, 10, 0);
        } else {
            $messages = $pd->getPublic($from, $node, 10, 0);
        }

        $this->view->assign('posts', $messages);
    }

    function getComments($post)
    {
        $pd = new \Modl\PostnDAO();
        return $pd->getComments($post);
    }
}
