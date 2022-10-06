<?php

use Movim\Widget\Base;
use Illuminate\Database\Capsule\Manager as DB;

use App\Contact;
use App\Message;
use App\Roster;

class Chats extends Base
{
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
        // Bug: In Chat::ajaxGet, Notification.current might come after this event
        // so we don't set the filter
        $this->registerEvent('chat_open', 'onChatOpen', /* 'chat'*/);
    }

    public function onStart($packet)
    {
        $tpl = $this->tpl();
        $tpl->cacheClear('_chats_item');
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

        // The following requests seems to be heavy for PostgreSQL
        // see https://stackoverflow.com/questions/40365098/why-is-postgres-not-using-my-index-on-a-simple-order-by-limit-1
        // a little hack is needed to use corectly the indexes
        if ($jid == false) {
            $message = $this->user->messages();

            $message = (DB::getDriverName() == 'pgsql')
                ? $message->orderByRaw('published desc nulls last')
                : $message->orderBy('published', 'desc');
            $message = $message->first();

            if ($message && $message->published) {
                $g->setStart(strtotime($message->published));
            } else {
                // We only sync up the last month the first time
                $g->setStart(\Carbon\Carbon::now()->subMonth()->timestamp);
            }

            $g->setLimit(250);
            $g->request();
        } elseif (validateJid($jid)) {
            $message = \App\Message::jid($jid);

            $message = (DB::getDriverName() == 'pgsql')
                ? $message->orderByRaw('published desc nulls last')
                : $message->orderBy('published', 'desc');
            $message = $message->first();

            if ($message && $message->published) {
                $g->setStart(strtotime($message->published));
            } else {
                $g->setLimit(150);
                $g->setBefore(true);
            }

            $g->setJid(echapJid($jid));
            $g->request();
        }
    }

    public function ajaxOpen($jid, $history = true)
    {
        if (!validateJid($jid)) {
            return;
        }

        $chats = \App\Cache::c('chats');

        if ($chats == null) {
            $chats = [];
        }

        unset($chats[$jid]);

        if ($jid != $this->user->id) {
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
        if (!validateJid($jid)) {
            return;
        }

        $chats = \App\Cache::c('chats');
        unset($chats[$jid]);
        \App\Cache::c('chats', $chats);

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
        foreach (array_reverse($chats) as $key => $value) {
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

            $rosters = $this->user->session->contacts()->whereIn('jid', array_keys($chats))
                            ->with('presence.capability')->get()->keyBy('jid');

            foreach (array_reverse($chats) as $key => $value) {
                $html .= $this->prepareChat($key, $contacts->get($key), $rosters->get($key), $messages->get($key));
            }
        }

        return $html;
    }

    public function prepareChat(string $jid, Contact $contact, Roster $roster = null,
        Message $message = null, string $status = null)
    {
        if (!validateJid($jid)) {
            return;
        }

        $view = $this->tpl();

        $view->assign('status', $status);
        $view->assign('contact', $contact);
        $view->assign('roster', $roster);
        $view->assign('count', $this->user->unreads($jid));

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
        $chats = \App\Cache::c('chats');
        $chats = is_array($chats) ? $chats : [];

        // Clean the unreads from the open ones
        foreach (array_keys($chats) as $jid) {
            if (array_key_exists($jid, $unreads)) {
                unset($unreads[$jid]);
            }
        }

        return array_merge(
            $unreads,
            is_array($chats) ? $chats : [],
        );
    }
}
