<?php

use Moxl\Xec\Action\Presence\Muc;
use Moxl\Xec\Action\Bookmark\Get;
use Moxl\Xec\Action\Bookmark\Set;

use Respect\Validation\Validator;

class Chats extends \Movim\Widget\Base
{
    function load()
    {
        $this->addcss('chats.css');
        $this->addjs('chats.js');
        $this->registerEvent('invitation', 'onMessage');
        $this->registerEvent('carbons', 'onMessage');
        $this->registerEvent('message', 'onMessage');
        $this->registerEvent('presence', 'onPresence', 'chat');
        $this->registerEvent('composing', 'onComposing');
        $this->registerEvent('paused', 'onPaused');
    }

    function onMessage($packet)
    {
        $message = $packet->content;

        if ($message->type != 'groupchat') {
            // If the message is from me
            if ($message->user_id == $message->jidto) {
                $from = $message->jidfrom;
            } else {
                $from = $message->jidto;
            }

            $this->ajaxOpen($from, false);
        }
    }

    function onPresence($packet)
    {
        if ($packet->content != null){
            $chats = \App\Cache::c('chats');
            if (is_array($chats) &&  array_key_exists($packet->content->jid, $chats)) {
                $this->rpc(
                    'MovimTpl.replace',
                    '#' . cleanupId($packet->content->jid.'_chat_item'),
                    $this->prepareChat($packet->content->jid));
                $this->rpc('Chats.refresh');

                $n = new Notification;
                $n->ajaxGet();
            }
        }
    }

    function onComposing($array)
    {
        $this->setState($array, $this->__('chats.composing'));
    }

    function onPaused($array)
    {
        $this->setState($array, $this->__('chats.paused'));
    }

    private function setState($array, $message)
    {
        list($from, $to) = $array;
        if ($from == $this->user->jid) {
            $jid = $to;
        } else {
            $jid = $from;
        }

        $this->rpc('MovimTpl.replace', '#' . cleanupId($jid.'_chat_item'), $this->prepareChat($jid, $message));
        $this->rpc('Chats.refresh');
    }

    function ajaxGet()
    {
        $this->rpc('MovimTpl.fill', '#chats_widget_list', $this->prepareChats());
        $this->rpc('Chats.refresh');
    }

    /**
     * @brief Get history
     */
    function ajaxGetHistory($jid = false)
    {
        $g = new \Moxl\Xec\Action\MAM\Get;

        if ($jid == false) {
            $message = $this->user->messages()
                                  ->orderBy('published', 'desc')
                                  ->first();
            if ($message) {
                $g->setStart(strtotime($message->published)+10);
            }

            $g->setLimit(150);
            $g->request();
        } elseif ($this->validateJid($jid)) {
            $message = $this->user->messages()
                                  ->where(function ($query) use ($jid) {
                                      $query->where('jidfrom', $jid)
                                            ->orWhere('jidto', $jid);
                                  })
                                  ->orderBy('published', 'desc')
                                  ->first();
            $g->setJid(echapJid($jid));

            if ($message) {
                // We add a little delay of 10sec to prevent some sync issues
                $g->setStart(strtotime($message->published)+10);
            }

            $g->request();
        }
    }

    function ajaxOpen($jid, $history = true)
    {
        if (!$this->validateJid($jid)) return;

        $chats = \App\Cache::c('chats');
        if ($chats == null) $chats = [];

        unset($chats[$jid]);

        if (/*!array_key_exists($jid, $chats)
                && */$jid != $this->user->jid) {
            $chats[$jid] = 1;

            if ($history) $this->ajaxGetHistory($jid);

            \App\Cache::c('chats', $chats);
            $this->rpc('Chats.prepend', $jid, $this->prepareChat($jid));
        }
    }

    function ajaxClose($jid)
    {
        if (!$this->validateJid($jid)) return;

        $chats = \App\Cache::c('chats');
        unset($chats[$jid]);
        \App\Cache::c('chats', $chats);

        $this->rpc('MovimTpl.remove', '#' . cleanupId($jid . '_chat_item'));

        $this->rpc('Chats.refresh');
        $this->rpc('Chat.empty');
        $this->rpc('MovimTpl.hidePanel');
    }

    function prepareChats()
    {
        $chats = \App\Cache::c('chats');

        $view = $this->tpl();

        if (!isset($chats)) {
            return '';
        }

        $view->assign('chats', array_reverse($chats));

        return $view->draw('_chats', true);
    }

    function prepareChat($jid, $status = null)
    {
        if (!$this->validateJid($jid)) return;

        $view = $this->tpl();

        $contact = App\Contact::find($jid);
        $view->assign('status', $status);
        $view->assign('contact', $contact ? $contact : new App\Contact(['id' => $jid]));
        $view->assign('roster', $this->user->session->contacts()->where('jid', $jid)->with('presence.capability')->first());
        $view->assign('message', $this->user->messages()
                                            ->where(function ($query) use ($jid) {
                                                $query->where('jidfrom', $jid)
                                                      ->orWhere('jidto', $jid);
                                            })
                                            ->orderBy('published', 'desc')
                                            ->first());
        return $view->draw('_chats_item', true);
    }

    private function validateJid($jid)
    {
        return (Validator::stringType()
            ->noWhitespace()
            ->length(6, 80)
            ->validate($jid));
    }
}
