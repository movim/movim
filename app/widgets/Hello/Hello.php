<?php

class Hello extends WidgetCommon
{
    function load()
    {
        $this->addcss('hello.css');
    }

    function ajaxChat($jid)
    {
        $c = new Chats;
        $c->ajaxOpen($jid);
        
        RPC::call('movim_redirect', $this->route('chat'));
    }

    function display()
    {
        $cd = new modl\ContactDAO;
        $this->view->assign('top', $cd->getTop(6));

        $pd = new \Modl\PostnDAO;
        $this->view->assign('news', $pd->getAllPosts(false, 0, 4));
    }
}
