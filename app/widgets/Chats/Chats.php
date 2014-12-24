<?php

class Chats extends WidgetCommon
{
    function load()
    {
        $this->addjs('chats.js');
        $this->registerEvent('carbons', 'onMessage');
        $this->registerEvent('message', 'onMessage');
    }

    function onMessage($packet)
    {
        $message = $packet->content;

        // If the message is from me
        if($message->session == $message->jidto) {
            $from = $message->jidfrom;
        } else {
            $from = $message->jidto;
        }

        $chats = Cache::c('chats');
        if(!array_key_exists($from, $chats)) {
            $this->ajaxOpen($from);
        }
    }

    function ajaxOpen($jid)
    {
        $chats = Cache::c('chats');
        if($chats == null) $chats = array();
         
        $chats[$jid] = 1;
        Cache::c('chats', $chats);

        RPC::call('movim_fill', 'chats_widget_list', $this->prepareChats());

        RPC::call('Chats.refresh');
    }

    function ajaxClose($jid)
    {
        $chats = Cache::c('chats');
        unset($chats[$jid]);
        Cache::c('chats', $chats);

        $c = new Chat;
        $c->ajaxGet(current(array_keys($chats)));

        RPC::call('movim_fill', 'chats_widget_list', $this->prepareChats());

        RPC::call('Chats.refresh');
    }

    function prepareChats()
    {
        $chats = Cache::c('chats');
        
        $view = $this->tpl();

        $cd = new \Modl\ContactDAO;

        foreach($chats as $jid => $value) {
            $chats[$jid] = $cd->get($jid);
        }

        $view->assign('chats', array_reverse($chats));
        
        return $view->draw('_chats', true);
    }

    function display()
    {
        $this->view->assign('list', $this->prepareChats());
    }
}
