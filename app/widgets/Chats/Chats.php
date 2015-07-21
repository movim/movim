<?php

use Moxl\Xec\Action\Presence\Muc;
use Moxl\Xec\Action\Bookmark\Get;
use Moxl\Xec\Action\Bookmark\Set;

use Respect\Validation\Validator;

class Chats extends WidgetBase
{
    function load()
    {
        $this->addcss('chats.css');
        $this->addjs('chats.js');
        $this->registerEvent('carbons', 'onMessage');
        $this->registerEvent('message', 'onMessage');
        $this->registerEvent('presence', 'onPresence', 'chat');
    }

    function onMessage($packet)
    {
        $message = $packet->content;

        if($message->type != 'groupchat') {
            // If the message is from me
            if($message->session == $message->jidto) {
                $from = $message->jidfrom;
            } else {
                $from = $message->jidto;
            }

            $this->ajaxOpen($from, false);
            /*
            $chats = Cache::c('chats');
            if(!array_key_exists($from, $chats)) {
                $this->ajaxOpen($from);
            } else {
                RPC::call('Chats.prepend', $from, $this->prepareChat($from));
            }*/
        }
    }

    function onPresence($packet)
    {
        $contacts = $packet->content;
        if($contacts != null){
            $c = $contacts[0];
            $chats = Cache::c('chats');
            if(is_array($chats) &&  array_key_exists($c->jid, $chats)) {
                RPC::call('movim_replace', $c->jid.'_chat_item', $this->prepareChat($c->jid));
                RPC::call('Chats.refresh');

                $n = new Notification;
                $n->ajaxGet();
            }
        }
    }

    /**
     * @brief Get history
     */
    function ajaxGetHistory($jid)
    {
        if(!$this->validateJid($jid)) return;

        $md = new \Modl\MessageDAO();
        $messages = $md->getContact(echapJid($jid), 0, 1);

        $g = new \Moxl\Xec\Action\MAM\Get;
        $g->setJid($jid);

        if(!empty($messages)) {
            $g->setStart(strtotime($messages[0]->published));
        }

        $g->request();
    }

    function ajaxOpen($jid, $history = true)
    {
        if(!$this->validateJid($jid)) return;

        $chats = Cache::c('chats');
        if($chats == null) $chats = array();

        unset($chats[$jid]);

        if(/*!array_key_exists($jid, $chats)
                && */$jid != $this->user->getLogin()) {
            $chats[$jid] = 1;

            if($history) $this->ajaxGetHistory($jid);

            Cache::c('chats', $chats);
            RPC::call('Chats.prepend', $jid, $this->prepareChat($jid));
        }
    }

    function ajaxClose($jid)
    {
        if(!$this->validateJid($jid)) return;

        $chats = Cache::c('chats');
        unset($chats[$jid]);
        Cache::c('chats', $chats);

        RPC::call('movim_delete', $jid.'_chat_item');

        RPC::call('Chats.refresh');
        RPC::call('Chat.empty');
        RPC::call('MovimTpl.hidePanel');
    }

    /**
     * @brief Display the add chat form
     */
    function ajaxAdd()
    {
        $view = $this->tpl();

        $cd = new \Modl\ContactDAO;
        $chats = Cache::c('chats');

        if(!isset($chats)) $chats = array();

        $view->assign('chats', array_keys($chats));
        $view->assign('top', $cd->getTop(15));
        $view->assign('presencestxt', getPresencesTxt());

        Dialog::fill($view->draw('_chats_add', true), true);
    }

    /**
     * @brief Display the extended list
     */
    function ajaxAddExtend()
    {
        $view = $this->tpl();

        $cd = new \Modl\ContactDAO;
        $contacts = $cd->getRosterSimple();
        $view->assign('contacts', $contacts);

        RPC::call('movim_fill', 'add_extend', $view->draw('_chats_add_extend', true));
    }

    function prepareChats()
    {
        $chats = Cache::c('chats');

        if(!isset($chats)) $chats = array();

        $view = $this->tpl();
        $view->assign('chats', array_reverse($chats));

        return $view->draw('_chats', true);
    }

    function prepareChat($jid)
    {
        if(!$this->validateJid($jid)) return;

        $view = $this->tpl();

        $cd = new \Modl\ContactDAO;
        $md = new \modl\MessageDAO();
        $cad = new \modl\CapsDAO();

        $presencestxt = getPresencesTxt();

        $cr = $cd->getRosterItem($jid);
        if(isset($cr)) {
            if($cr->value != null) {
                $view->assign('presence', $presencestxt[$cr->value]);
            }
            $view->assign('contact', $cr);
            $view->assign('caps', $cad->get($cr->node.'#'.$cr->ver));
        } else {
            $view->assign('contact', $cd->get($jid));
            $view->assign('caps', null);
        }

        $m = $md->getContact($jid, 0, 1);
        if(isset($m)) {
            $view->assign('message', $m[0]);
        }

        return $view->draw('_chats_item', true);
    }

    private function validateJid($jid)
    {
        $validate_jid = Validator::string()->noWhitespace()->length(6, 40);

        if($validate_jid->validate($jid)) return true;
        else return false;
    }

    function display()
    {
        $this->view->assign('list', $this->prepareChats());
    }
}
