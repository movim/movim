<?php

class Chat extends WidgetCommon
{
    function load()
    {
    }

    function ajaxGet($jid)
    {
        $html = $this->prepareChat($jid);
        
        $header = $this->prepareHeader($jid);
        
        Header::fill($header);
        RPC::call('movim_fill', 'chat_widget', $html);
    }

    function prepareHeader($jid)
    {
        $view = $this->tpl();
        
        $view->assign('jid', $jid);

        return $view->draw('_chat_header', true);
    }

    function prepareChat($jid)
    {
        $md = new \Modl\MessageDAO();
        $messages = $md->getContact(echapJid($jid), 0, 10);
        $messages = array_reverse($messages);
        
        $cd = new \Modl\ContactDAO;

        $view = $this->tpl();
        
        $view->assign('jid', $jid);
        $view->assign('contact', $cd->get($jid));
        $view->assign('me', $cd->get());
        $view->assign('messages', $messages);

        return $view->draw('_chat', true);
    }

    function display()
    {

    }
}
