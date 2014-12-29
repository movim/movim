<?php

use Moxl\Xec\Action\Message\Publish;

class Chat extends WidgetCommon
{
    function load()
    {
        $this->addjs('chat.js');
        $this->registerEvent('carbons', 'onMessage');
        $this->registerEvent('message', 'onMessage');
        $this->registerEvent('composing', 'onComposing');
        $this->registerEvent('paused', 'onPaused');
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

        RPC::call('movim_fill', $from.'_messages', $this->prepareMessages($from));        
        RPC::call('MovimTpl.scrollPanel');
    }

    function onComposing($array)
    {
        list($from, $to) = $array;
        $myself = false;

        if($from == $this->user->getLogin()) {
            // If the message is from me
            $myself = true;
            $jid = $to;
        } else {
            $jid = $from;
        }
        
        RPC::call('movim_fill', $jid.'_messages', $this->prepareMessages($jid, 'composing', $myself));
        RPC::call('MovimTpl.scrollPanel');
    }

    function onPaused($array)
    {
        list($from, $to) = $array;
        $myself = false;

        if($from == $this->user->getLogin()) {
            // If the message is from me
            $myself = true;
            $jid = $to;
        } else {
            $jid = $from;
        }
        
        RPC::call('movim_fill', $jid.'_messages', $this->prepareMessages($jid, 'paused', $myself));
        RPC::call('MovimTpl.scrollPanel');
    }

    /**
     * @brief Show the smiley list
     */
    function ajaxSmiley()
    {
        $view = $this->tpl();
        Dialog::fill($view->draw('_chat_smiley', true));
    }

    /**
     * @brief Get a discussion
     * @parem string $jid
     */
    function ajaxGet($jid)
    {
        $html = $this->prepareChat($jid);
        
        $header = $this->prepareHeader($jid);
        
        Header::fill($header);
        RPC::call('movim_fill', 'chat_widget', $html);
        RPC::call('MovimTpl.scrollPanel');
    }

    /**
     * @brief Send a message
     *
     * @param string $to
     * @param string $message
     * @return void
     */
    function ajaxSendMessage($to, $message, $muc = false, $ressource = false) {
        if($message == '')
            return;
        
        $m = new \Modl\Message();
        $m->session = $this->user->getLogin();
        $m->jidto   = echapJid($to);
        $m->jidfrom = $this->user->getLogin();
        
        $session    = \Sessionx::start();
        
        $m->type    = 'chat';
        $m->ressource = $session->ressource;
        
        if($muc) {
            $m->type        = 'groupchat';
            $m->ressource   = $session->user;
            $m->jidfrom     = $to;
        }
        
        $m->body      = rawurldecode($message);
        $m->published = date('Y-m-d H:i:s');
        $m->delivered = date('Y-m-d H:i:s');
        
        $md = new \Modl\MessageDAO();
        $md->set($m);

        /* Is it really clean ? */
        $packet = new Moxl\Xec\Payload\Packet;
        $packet->content = $m;
        $this->onMessage($packet, true);

        if($ressource != false) {
            $to = $to . '/' . $ressource;
        }

        // We decode URL codes to send the correct message to the XMPP server
        $m = new Publish;
        $m->setTo($to);
        $m->setContent(htmlspecialchars(rawurldecode($message)));

        /*if($muc) {
            $m->setMuc();
        }*/

        $m->request();
    }

    function prepareHeader($jid)
    {
        $view = $this->tpl();

        $cd = new \Modl\ContactDAO;

        $cr = $cd->getRosterItem($jid);
        if(isset($cr)) {
            $contact = $cr;
        } else {
            $contact = $cd->get($jid);
        }
        
        $view->assign('contact', $contact);
        $view->assign('presences', getPresences());
        $view->assign('jid', $jid);

        return $view->draw('_chat_header', true);
    }

    function prepareChat($jid)
    {
        $view = $this->tpl();
        
        $view->assign('jid', $jid);
        $view->assign('messages', $this->prepareMessages($jid));

        $view->assign(
            'send',
            $this->call(
                'ajaxSendMessage',
                "'" . $jid . "'",
                "Chat.sendMessage(this, '" . $jid . "')")
            );
        $view->assign('smiley', $this->call('ajaxSmiley'));

        return $view->draw('_chat', true);
    }

    function prepareMessages($jid, $status = false, $myself = false)
    {
        $md = new \Modl\MessageDAO();
        $messages = $md->getContact(echapJid($jid), 0, 15);
        $messages = array_reverse($messages);
        
        $cd = new \Modl\ContactDAO;
        $view = $this->tpl();

        $contact = $cd->get($jid);
        if($contact != null) {
            RPC::call(
                'Chat.notify',
                $contact->getTrueName(),
                trim($messages[0]->body),
                $contact->getPhoto('m'));
        }
        
        $view->assign('jid', $jid);
        $view->assign('contact', $contact);
        $view->assign('me', $cd->get());
        $view->assign('messages', $messages);

        $view->assign('status', $status);
        $view->assign('myself', $myself);

        return $view->draw('_chat_messages', true);
    }

    function display()
    {

    }
}
