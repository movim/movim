<?php

namespace App\Widgets\Chats;

use Movim\Widget\Base;
use Illuminate\Database\Capsule\Manager as DB;

use App\Contact;
use App\Message;
use App\Roster;
use Movim\CurrentCall;

class Chats extends Base
{
    private $_filters = ['all', 'roster'];

    public function load()
    {
        $this->addcss('chats.css');
        $this->addjs('chats.js');
        $this->registerEvent('session_start_handle', 'onStart');
        $this->registerEvent('receiptack', 'onMessage', 'chat');
        $this->registerEvent('displayed', 'onMessage', 'chat');
        $this->registerEvent('retracted', 'onMessage', 'chat');
        $this->registerEvent('carbons', 'onMessage');
        $this->registerEvent('message', 'onMessage');
        $this->registerEvent('jingle_message', 'onMessage');
        $this->registerEvent('presence', 'onPresence'/*, 'chat'*/);
        $this->registerEvent('chatstate', 'onChatState', 'chat');
        // Bug: In Chat::ajaxGet, Notif.current might come after this event
        // so we don't set the filter
        $this->registerEvent('chat_open', 'onChatOpen', /* 'chat'*/);

        $this->registerEvent('currentcall_started', 'onCallEvent', 'chat');
        $this->registerEvent('currentcall_stopped', 'onCallEvent', 'chat');
    }

    public function onStart($packet)
    {
        $tpl = $this->tpl();
        $tpl->cacheClear('_chats_item');
    }

    public function onMessage($packet)
    {
        $message = $packet->content;

        if (!$message->isMuc()) {
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
        if (!validateJid($jid)) {
            return;
        }

        $this->rpc(
            'MovimTpl.replace',
            '#' . cleanupId($jid . '_chat_item'),
            $this->prepareChat(
                $jid,
                $this->resolveContactFromJid($jid),
                $this->resolveRosterFromJid($jid),
                $this->resolveMessageFromJid($jid)
            )
        );
        $this->rpc('Chats.setActive', $jid);
        $this->rpc('Chats.refresh');
    }

    public function onPresence($packet)
    {
        if ($packet->content != null) {
            $this->replaceChat($packet->content->jid);
        }
    }

    public function onCallEvent($packet)
    {
        $this->replaceChat($packet[0]);
    }

    private function replaceChat(string $jid)
    {
        if ($this->user->openChats()->where('jid', $jid)->count() > 0) {
            $this->rpc(
                'MovimTpl.replace',
                '#' . cleanupId($jid . '_chat_item'),
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

    public function onChatState(array $array)
    {
        $this->setState($array[0], isset($array[1]));
    }

    private function setState(string $jid, bool $composing)
    {
        if ($this->user->openChats()->where('jid', $jid)->count() > 0) {
            $this->rpc(
                $composing
                    ? 'MovimUtils.addClass'
                    : 'MovimUtils.removeClass',
                '#' . cleanupId($jid . '_chat_item') . ' span.primary',
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
     * @brief Get MAM history
     */
    public function ajaxGetMAMHistory(?string $jid = null)
    {
        $g = new \Moxl\Xec\Action\MAM\Get;

        // The following requests seems to be heavy for PostgreSQL
        // see https://stackoverflow.com/questions/40365098/why-is-postgres-not-using-my-index-on-a-simple-order-by-limit-1
        // a little hack is needed to use corectly the indexes
        if ($jid == null) {
            $message = $this->user->messages();

            $message = (DB::getDriverName() == 'pgsql')
                ? $message->orderByRaw('published desc nulls last')
                : $message->orderBy('published', 'desc');
            $message = $message->first();

            if ($message && $message->published) {
                $g->setStart(strtotime($message->published) + 1);
            } else {
                // We only sync up the last month the first time
                $g->setStart(\Carbon\Carbon::now()->subMonth()->timestamp);
            }

            $g->request();
        } elseif (validateJid($jid)) {
            $message = \App\Message::jid($jid);

            $message = (DB::getDriverName() == 'pgsql')
                ? $message->orderByRaw('published asc nulls last')
                : $message->orderBy('published', 'asc');
            $message = $message->first();

            if ($message && $message->published) {
                $g->setEnd(strtotime($message->published));
            }

            $g->setLimit(150);
            $g->setBefore('');
            $g->setJid(echapJid($jid));
            $g->request();
        }
    }

    public function ajaxSetFilter(string $filter)
    {
        if (in_array($filter, $this->_filters)) {
            $this->user->chats_filter = $filter;
            $this->user->save();
        }

        $this->rpc('Chats.refreshFilters');
    }

    public function ajaxOpen($jid, $history = true)
    {
        if (!validateJid($jid) || $jid != $this->user->id) {
            if ($history) {
                $this->ajaxGetMAMHistory($jid);
            }

            $openChat = $this->user->openChats()->firstOrCreate(['jid' => $jid]);
            $openChat->touch();

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
        if (!validateJid($jid)) {
            return;
        }

        $this->user->openChats()->where('jid', $jid)->delete();

        $tpl = $this->tpl();
        $tpl->cacheClear('_chats_item', $jid);

        $this->rpc('MovimTpl.remove', '#' . cleanupId($jid . '_chat_item'));
        $this->rpc('Chat_ajaxClearCounter', $jid);
        $this->rpc('Chats.refresh');

        if ($closeDiscussion) {
            $this->rpc('Chat_ajaxGet');
        }
    }

    public function prepareChats()
    {
        $chats = $this->resolveChats();

        if (!isset($chats)) {
            return '';
        }

        $html = '';
        $view = $this->tpl();

        // Check if we got already in the cache
        foreach (array_reverse($chats) as $key) {
            $cached = $view->cached('_chats_item', $key);

            if ($cached) {
                $html .= $cached;
            } else if (validateJid($key)) {
                $html = '';
                break;
            }
        }

        // If not we fully rebuild it
        if ($html == '') {
            $view->cacheClear('_chats_item');

            $contacts = \App\Contact::whereIn('id', $chats)->get()->keyBy('id');

            foreach ($chats as $jid) {
                if (!$contacts->has($jid)) {
                    $contacts->put($jid, new \App\Contact(['id' => $jid]));
                }
            }

            $messages = collect();

            $jidFromToMessages = DB::table('messages')
                ->where('user_id', $this->user->id)
                ->whereIn('jidfrom', $chats)
                ->unionAll(
                    DB::table('messages')
                        ->where('user_id', $this->user->id)
                        ->whereIn('jidto', $chats)
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

            $rosters = $this->user->session->contacts()->whereIn('jid', $chats)
                ->with('presence.capability')->get()->keyBy('jid');

            foreach (array_reverse($chats) as $key) {
                $html .= $this->prepareChat($key, $contacts->get($key), $rosters->get($key), $messages->get($key));
            }
        }

        return $html;
    }

    public function prepareChat(
        string $jid,
        Contact $contact,
        Roster $roster = null,
        Message $message = null,
        string $status = null
    ) {
        if (!validateJid($jid)) {
            return;
        }

        $view = $this->tpl();

        $view->assign('status', $status);
        $view->assign('contact', $contact);
        $view->assign('roster', $roster);
        $view->assign('count', $this->user->unreads($jid));
        $view->assign('contactincall', CurrentCall::getInstance()->isJidInCall($jid));

        if ($status == null) {
            $view->assign('message', $message);
        }

        return $view->cache('_chats_item', $jid);
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

    private function resolveChats(): array
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
        $openChats = $this->user->openChats()->orderBy('updated_at')->pluck('jid')->toArray();

        // Clean the unreads from the open ones
        foreach ($openChats as $jid) {
            if (array_key_exists($jid, $unreads)) {
                unset($unreads[$jid]);
            }
        }

        return array_merge(
            $unreads,
            $openChats,
        );
    }

    function display()
    {
        $this->view->assign('filters', $this->_filters);
        $this->view->assign('filter', $this->user->chats_filter);
    }
}
