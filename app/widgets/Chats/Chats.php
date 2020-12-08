<?php

use Movim\Widget\Base;
use Illuminate\Database\Capsule\Manager as DB;

use Respect\Validation\Validator;

use App\Contact;
use App\Message;
use App\Roster;

class Chats extends Base
{
    public function load()
    {
        $this->addcss('chats.css');
        $this->addjs('chats.js');
        $this->registerEvent('receiptack', 'onMessage', 'chat');
        $this->registerEvent('displayed', 'onMessage', 'chat');
        $this->registerEvent('retracted', 'onMessage', 'chat');
        $this->registerEvent('carbons', 'onMessage');
        $this->registerEvent('message', 'onMessage');
        $this->registerEvent('presence', 'onPresence', 'chat');
        $this->registerEvent('chatstate', 'onChatState', 'chat');
        $this->registerEvent('chat_open', 'onChatOpen', 'chat');
    }

    public function onMessage($packet)
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

    public function onChatOpen($jid)
    {
        if (!$this->validateJid($jid)) {
            return;
        }

        $this->rpc(
            'MovimTpl.replace',
            '#' . cleanupId($jid . '_chat_item'),
            $this->prepareChat(
                $jid,
                $this->resolveContactFromJid($jid),
                $this->resolveRosterFromJid($jid),
                $this->resolveMessageFromJid($jid),
                null,
                true
            )
        );
        $this->rpc('Chats.refresh');
    }

    public function onPresence($packet)
    {
        if ($packet->content != null) {
            $chats = \App\Cache::c('chats');
            if (is_array($chats) &&  array_key_exists($packet->content->jid, $chats)) {
                $this->rpc(
                    'MovimTpl.replace',
                    '#' . cleanupId($packet->content->jid.'_chat_item'),
                    $this->prepareChat(
                        $packet->content->jid,
                        $this->resolveContactFromJid($packet->content->jid),
                        $this->resolveRosterFromJid($packet->content->jid),
                        $this->resolveMessageFromJid($packet->content->jid)
                    )
                );
                $this->rpc('Chats.refresh');
            }
        }
    }

    public function onChatState(array $array)
    {
        $this->setState($array[0], isset($array[1]));
    }

    private function setState(string $jid, bool $composing)
    {
        $chats = \App\Cache::c('chats');
        if (is_array($chats) &&  array_key_exists($jid, $chats)) {
            $this->rpc(
                $composing
                    ? 'MovimUtils.addClass'
                    : 'MovimUtils.removeClass',
                '#' . cleanupId($jid.'_chat_item') . ' span.primary',
                'composing'
            );
        }
    }

    public function ajaxHttpGet()
    {
        $this->rpc('MovimTpl.fill', '#chats_widget_list', $this->prepareChats());
        $this->rpc('Chats.refresh');
    }

    /**
     * @brief Get history
     */
    public function ajaxGetHistory($jid = false)
    {
        $g = new \Moxl\Xec\Action\MAM\Get;

        if ($jid == false) {
            $message = $this->user->messages()
                                  ->orderBy('published', 'desc')
                                  ->first();
            if ($message) {
                $g->setStart(strtotime($message->published));
            }

            $g->setLimit(150);
            $g->request();
        } elseif ($this->validateJid($jid)) {
            $message = \App\Message::jid($jid)
                ->orderBy('published', 'desc')
                ->first();
            $g->setJid(echapJid($jid));

            if ($message) {
                $g->setStart(strtotime($message->published));
            }

            $g->request();
        }
    }

    public function ajaxOpen($jid, $history = true)
    {
        if (!$this->validateJid($jid)) {
            return;
        }

        $chats = \App\Cache::c('chats');
        if ($chats == null) {
            $chats = [];
        }

        unset($chats[$jid]);

        if (/*!array_key_exists($jid, $chats)
                && */$jid != $this->user->id) {
            $chats[$jid] = 1;

            if ($history) {
                $this->ajaxGetHistory($jid);
            }

            \App\Cache::c('chats', $chats);
            $this->rpc(
                'Chats.prepend',
                $jid,
                $this->prepareChat(
                    $jid,
                    $this->resolveContactFromJid($jid),
                    $this->resolveRosterFromJid($jid),
                    $this->resolveMessageFromJid($jid)
                )
            );

            $this->rpc('Chats.refresh');
        }
    }

    public function ajaxClose($jid, $closeDiscussion = false)
    {
        if (!$this->validateJid($jid)) {
            return;
        }

        $chats = \App\Cache::c('chats');
        unset($chats[$jid]);
        \App\Cache::c('chats', $chats);

        $this->rpc('MovimTpl.remove', '#' . cleanupId($jid . '_chat_item'));
        $this->rpc('Chat_ajaxClearCounter', $jid);
        $this->rpc('Chats.refresh');

        if ($closeDiscussion) {
            $this->rpc('Chat_ajaxGet');
        }
    }

    public function prepareChats($emptyItems = false)
    {
        $unreads = [];

        // Get the chats with unread messages
        $this->user->messages()
            ->select('jidfrom')
            ->where('seen', false)
            ->whereIn('type', ['chat', 'headline', 'invitation'])
            ->where('jidfrom', '!=', $this->user->id)
            ->groupBy('jidfrom')
            ->pluck('jidfrom')
            ->each(function ($item) use (&$unreads) {
                $unreads[$item] = 1;
            });

        // Append the open chats
        $chats = \App\Cache::c('chats');

        $chats = array_merge(
            is_array($chats) ? $chats : [],
            $unreads
        );

        $view = $this->tpl();

        if (!isset($chats)) {
            return '';
        }

        if ($emptyItems == false) {
            $contacts = App\Contact::whereIn('id', array_keys($chats))->get()->keyBy('id');
            foreach (array_keys($chats) as $jid) {
                if (!$contacts->has($jid)) {
                    $contacts->put($jid, new App\Contact(['id' => $jid]));
                }
            }

            $messages = collect();

            $jidFromToMessages = DB::table('messages')
                ->where('user_id', $this->user->id)
                ->whereIn('jidfrom', array_keys($chats))
                ->unionAll(DB::table('messages')
                    ->where('user_id', $this->user->id)
                    ->whereIn('jidto', array_keys($chats))
                );

            $selectedMessages = $this->user->messages()
                ->joinSub(
                    function ($query) use ($jidFromToMessages) {
                        $query->selectRaw('max(published) as published, jidfrom, jidto')
                            ->from($jidFromToMessages, 'messages')
                            ->where('user_id', $this->user->id)
                            ->groupBy(['jidfrom', 'jidto']);
                    },
                    'recents',
                    function ($join) {
                        $join->on('recents.published', 'messages.published')
                             ->on('recents.jidfrom', 'messages.jidfrom')
                             ->on('recents.jidto', 'messages.jidto');
                    }
                )->get();

            foreach ($selectedMessages as $message) {
                $key = $message->jidfrom == $this->user->id
                    ? $message->jidto
                    : $message->jidfrom;

                // $selectedMessages contains jidfrom and jidto together, we only take the most recent
                if (!$messages->has($key) || $message->published > $messages->get($key)->published) {
                    $messages->put($key, $message);
                }
            }

            $view->assign('rosters', $this->user->session->contacts()->whereIn('jid', array_keys($chats))
                                        ->with('presence.capability')->get()->keyBy('jid'));
            $view->assign('contacts', $contacts);
            $view->assign('messages', $messages);
        }

        $view->assign('chats', array_reverse($chats));
        $view->assign('emptyItems', $emptyItems);

        return $view->draw('_chats');
    }

    public function prepareEmptyChat($jid)
    {
        $view = $this->tpl();
        $view->assign('jid', $jid);
        return $view->draw('_chats_empty_item');
    }

    public function prepareChat(string $jid, Contact $contact, Roster $roster = null,
        Message $message = null, string $status = null, bool $active = false)
    {
        if (!$this->validateJid($jid)) {
            return;
        }

        $view = $this->tpl();

        $view->assign('status', $status);
        $view->assign('contact', $contact);
        $view->assign('roster', $roster);
        $view->assign('active', $active);
        $view->assign('count', $this->user->unreads($jid));

        if ($status == null) {
            $view->assign('message', $message);
        }

        return $view->draw('_chats_item');
    }

    public function resolveContactFromJid(string $jid): Contact
    {
        $contact = Contact::find($jid);
        return $contact ? $contact : new Contact(['id' => $jid]);
    }

    public function resolveRosterFromJid(string $jid): ?Roster
    {
        return $this->user->session->contacts()->where('jid', $jid)
                    ->with('presence.capability')->first();
    }

    public function resolveMessageFromJid(string $jid): ?Message
    {
        return \App\Message::jid($jid)
            ->orderBy('published', 'desc')
            ->first();
    }

    private function validateJid(string $jid): bool
    {
        return (Validator::stringType()
            ->noWhitespace()
            ->length(6, 80)
            ->validate($jid));
    }
}
