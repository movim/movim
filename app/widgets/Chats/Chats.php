<?php

use Moxl\Xec\Action\Presence\Muc;

class Chats extends WidgetCommon
{
    function load()
    {
        $this->addjs('chats.js');
        $this->registerEvent('carbons', 'onMessage');
        $this->registerEvent('message', 'onMessage');
        $this->registerEvent('presence', 'onPresence', 'chat');
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
            // TODO notification overwrite issue
            //RPC::call('movim_replace', $from, $this->prepareChat($from));
            RPC::call('Chats.refresh');
        }
    }

    function onPresence($packet)
    {
        $contacts = $packet->content;
        if($contacts != null){
            $c = $contacts[0];
            $chats = Cache::c('chats');
            if(array_key_exists($c->jid, $chats)) {
                RPC::call('movim_replace', $c->jid, $this->prepareChat($c->jid));
                RPC::call('Chats.refresh');
            }
        }
    }

    function ajaxOpen($jid)
    {
        $chats = Cache::c('chats');
        if($chats == null) $chats = array();

        if(!array_key_exists($key, $chats)) {
            $chats[$jid] = 1;
            Cache::c('chats', $chats);

            RPC::call('movim_prepend', 'chats_widget_list', $this->prepareChat($jid));
            RPC::call('Chats.refresh');
        }
    }

    function ajaxClose($jid)
    {
        $chats = Cache::c('chats');
        unset($chats[$jid]);
        Cache::c('chats', $chats);

        //$c = new Chat;
        //$c->ajaxGet(current(array_keys($chats)));
        RPC::call('movim_delete', $jid);

        RPC::call('Chats.refresh');
    }

    /**
     * @brief Display the add chat form
     */
    function ajaxAdd()
    {
        $view = $this->tpl();

        $cd = new \Modl\ContactDAO;
        $contacts = $cd->getRosterSimple();
        $view->assign('contacts', $contacts);

        Dialog::fill($view->draw('_chats_add', true), true);
    }

    /**
     * @brief Display the add room form
     */
    function ajaxAddRoom()
    {
        $view = $this->tpl();

        /*$view->assign('add', 
            $this->call(
                'ajaxAdd', 
                "movim_parse_form('add')"));
        $view->assign('search', $this->call('ajaxDisplayFound', 'this.value'));*/

        Dialog::fill($view->draw('_chats_add_room', true));
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

        $view = $this->tpl();

        $cod = new \modl\ConferenceDAO();

        $view->assign('conferences', $cod->getAll());
        $view->assign('chats', array_reverse($chats));
        
        return $view->draw('_chats', true);
    }

    function prepareChat($jid)
    {
        $view = $this->tpl();

        $cd = new \Modl\ContactDAO;
        $md = new \modl\MessageDAO();

        $presencestxt = getPresencesTxt();

        $cr = $cd->getRosterItem($jid);
        if(isset($cr)) {
            if($cr->value != null) {
                $view->assign('presence', $presencestxt[$cr->value]);
            }
            $view->assign('contact', $cr);
        } else {
            $view->assign('contact', $cd->get($jid));
        }

        $m = $md->getContact($jid, 0, 1);
        if(isset($m)) {
            $view->assign('message', $m[0]);
        }

        return $view->draw('_chats_item', true);
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
