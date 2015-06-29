<?php

class Hello extends WidgetBase
{
    function load()
    {
    }

    function ajaxChat($jid)
    {
        $c = new Chats;
        $c->ajaxOpen($jid);

        RPC::call('movim_redirect', $this->route('chat', $jid));
    }

    function display()
    {
        $cd = new modl\ContactDAO;
        $this->view->assign('top', $cd->getTop(6));

        $pd = new \Modl\PostnDAO;
        $this->view->assign('news', $pd->getAllPosts(false, 0, 4));

        $this->view->assign('jid', $this->user->getLogin());

        $this->view->assign('presencestxt', getPresencesTxt());
    }
}
