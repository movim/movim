<?php

use Moxl\Xec\Action\Presence\Muc;

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
        } else {
            RPC::call('movim_fill', 'chats_widget_list', $this->prepareChats());
            RPC::call('Chats.refresh');
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

    // Join a MUC 
    function ajaxBookmarkMucJoin($jid, $nickname)
    {
        $p = new Muc;
        $p->setTo($jid)
          ->setNickname($nickname)
          ->request();
    }

    function prepareChats()
    {
        $chats = Cache::c('chats');
        $messages = array();
        
        $view = $this->tpl();

        $cd = new \Modl\ContactDAO;
        $cod = new \modl\ConferenceDAO();
        $md = new \modl\MessageDAO();
        
        foreach($chats as $jid => $value) {
            $cr = $cd->getRosterItem($jid);
            if(isset($cr)) {
                $chats[$jid] = $cr;
            } else {
                $chats[$jid] = $cd->get($jid);
            }

            $m = $md->getContact($jid, 0, 1);
            if(isset($m)) {
                $messages[$jid] = $m[0];
            }
        }
        
        $view->assign('conferences', $cod->getAll());
        $view->assign('chats', array_reverse($chats));
        $view->assign('messages', $messages);
        
        return $view->draw('_chats', true);
    }

    function prepareChatrooms()
    {
        return $view->draw('_chatrooms', true);
    }

    function display()
    {
        $this->view->assign('list', $this->prepareChats());
    }
}
