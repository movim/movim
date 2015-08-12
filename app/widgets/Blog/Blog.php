<?php

class Blog extends WidgetBase {
    function load()
    {

    }

    function display()
    {
        /*if(!$this->get('f')) {
            return;
        }*/

        if($this->_view == 'grouppublic') {
            $from = $this->get('s');
            $node = $this->get('n');
            $this->view->assign('mode', 'group');
            $this->view->assign('server', $from);
            $this->view->assign('node', $node);

            $pd = new \Modl\ItemDAO;
            $this->view->assign('item', $pd->getItem($from, $node));
        } else {
            $from = $this->get('f');

            $cd = new \modl\ContactDAO();
            $c  = $cd->get($from, true);
            $this->view->assign('contact', $c);
            if(filter_var($from, FILTER_VALIDATE_EMAIL)) {
                $node = 'urn:xmpp:microblog:0';
            } else {
                return;
            }
            $this->view->assign('mode', 'blog');
        }

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
