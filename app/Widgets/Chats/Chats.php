<?php

namespace App\Widgets\Chats;

use Movim\Widget\Base;
use Illuminate\Database\Capsule\Manager as DB;

use App\Contact;
use App\Message;
use App\OpenChat;
use App\Roster;
use App\User;
use App\Widgets\Chat\Chat;
use Carbon\Carbon;
use Movim\CurrentCall;

use React\Promise\PromiseInterface;
use function React\Promise\resolve;

class Chats extends Base
{
    private $_filters = ['all', 'roster'];

    public function boot()
    {
        // Each day at 00:01
        $this->registerTask('1 0 * * *', 'cleanCache', function (): PromiseInterface {
            if (User::me()->id) {
                User::me()->openChats->each(function ($openChat) {
                    $view = $this->tpl();
                    $view->cacheClear('_chats_item', $openChat->jid);
                });
            }

            return resolve(true);
        });
    }

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

        $this->registerEvent('callinvitepropose', 'onCallInvite');
        $this->registerEvent('callinviteaccept', 'onCallInvite');
        $this->registerEvent('callinviteleft', 'onCallInvite');
    }

    public function onStart($packet)
    {
        $tpl = $this->tpl();
        $tpl->cacheClear('_chats_item');
    }

    public function onCallInvite($packet)
    {
        $this->rpc('MovimTpl.fill', '#chats_calls_list', $this->prepareCalls());
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

            $this->ajaxOpen($from, history: false);
        }
    }

    public function onChatOpen($jid)
    {
        if (!validateJid($jid)) {
            return;
        }

        $this->rpc(
            'MovimTpl.replace',
            $this->getItemId($jid),
            $this->prepareChat(
                $jid,
                $this->resolveContactFromJid($jid),
                $this->resolveRosterFromJid($jid),
                $this->resolveMessageFromJid($jid)
            )
        );
        $this->rpc('MovimUtils.addClass', $this->getItemId($jid), 'active');
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
                $this->getItemId($jid),
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
                $this->getItemId($jid) . ' span.primary',
                'composing'
            );
        }
    }

    public function ajaxHttpGet()
    {
        $this->rpc('MovimTpl.fill', '#chats', $this->prepareChats());
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
                // We sync up the last 500 messages at first
                $g->setLimit(500);
                $g->setBefore('');
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
            $g->setJid($jid);
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

    public function ajaxOpen($jid, ?bool $andShow = false, ?bool $history = true)
    {
        if (!validateJid($jid) || $jid != $this->user->id) {
            if ($history) {
                $this->ajaxGetMAMHistory($jid);
            }

            $openChat = $this->user->openChats()->firstOrCreate(['jid' => $jid]);
            $openChat->touch();

            $this->rpc('MovimTpl.remove', $this->getItemId($jid));
            $this->rpc('MovimTpl.prepend', '#chats', $this->prepareChat(
                $jid,
                $this->resolveContactFromJid($jid),
                $this->resolveRosterFromJid($jid),
                $this->resolveMessageFromJid($jid)
            ));
            $this->rpc('Chats.refresh');
            $this->rpc('Chats.setActive', $jid);

            if ($andShow) {
                $this->rpc('Chat.get', $jid);
            }
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

        $this->rpc('MovimTpl.remove', $this->getItemId($jid));
        $this->rpc('Chats.refresh');

        // Clear the counter
        (new Chat)->getMessages($jid, seenOnly: true, event: false);

        if ($closeDiscussion) {
            $this->rpc('Chat_ajaxGet');
        }

        $this->rpc('Stories_ajaxHttpGet');
    }

    public function prepareCalls()
    {
        $view = $this->tpl();
        $view->assign('calls', $this->user->session->mujiCalls()->where('isfromconference', false)->get());

        return $view->draw('_chats_calls');
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
        ?Roster $roster = null,
        ?Message $message = null,
        ?string $status = null
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
        $toOpen = [];

        $this->user->messages()
            ->select((DB::raw('max(published) as published, jidfrom, jidto')))
            ->where('seen', false)
            ->whereIn('type', ['chat', 'headline', 'invitation'])
            ->whereNotIn('jidfrom', function ($query) {
                $query->select('jid')
                    ->from('open_chats')
                    ->where('user_id', me()->id);
            })
            ->whereNotIn('jidto', function ($query) {
                $query->select('jid')
                    ->from('open_chats')
                    ->where('user_id', me()->id);
            })
            ->groupBy('jidfrom', 'jidto')
            ->get()
            ->each(function ($message) use (&$toOpen) {
                $jid = $message->jidfrom == me()->id
                    ? $message->jidto
                    : $message->jidfrom;

                if (array_key_exists($jid, $toOpen)) {
                    if ((new Carbon($toOpen[$jid]->published))->isBefore(new Carbon($message->published))) {
                        $toOpen[$jid] = $message->published;
                    }
                } else {
                    $toOpen[$jid] = $message->published;
                }
            });

        foreach ($toOpen as $jid => $published) {
            $openChat = new OpenChat;
            $openChat->user_id = me()->id;
            $openChat->jid = $jid;
            $openChat->created_at = $openChat->updated_at = $published;
            $openChat->save(['timestamps' => false]);

            $view = $this->tpl();
            $view->cacheClear('_chats_item', $jid);
        }

        return $this->user->openChats()->orderBy('updated_at')->pluck('jid')->toArray();
    }

    public function display()
    {
        $this->view->assign('filters', $this->_filters);
        $this->view->assign('filter', $this->user->chats_filter);
    }

    private function getItemId(string $jid): string
    {
        return '#' . cleanupId(slugify($jid) . '_chat_item');
    }
}
