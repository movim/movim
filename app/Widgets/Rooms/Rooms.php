<?php

namespace App\Widgets\Rooms;

use Moxl\Xec\Action\Disco\Request;
use Moxl\Xec\Action\Presence\Muc;
use Illuminate\Database\Capsule\Manager as DB;

use Movim\Widget\Base;

use App\Conference;
use App\Member;
use Movim\Widget\Wrapper;
use Moxl\Xec\Payload\Packet;
use Moxl\Xec\Action\Presence\Unavailable;

class Rooms extends Base
{
    public function load()
    {
        $this->addcss('rooms.css');
        $this->addjs('rooms.js');

        $this->registerEvent('bookmark2_get_handle', 'onBookmarkGet');
        $this->registerEvent('bookmark2', 'onBookmarkSet');
        $this->registerEvent('bookmark2_retract', 'onBookmarkRetract');
        $this->registerEvent('bookmark2_set_handle', 'onBookmarkSet');
        $this->registerEvent('bookmark2_delete_handle', 'onBookmarkSet');

        $this->registerEvent('disco_request_handle', 'onDiscoRequest');

        $this->registerEvent('muc_destroy_handle', 'onDestroyed', 'chat');

        $this->registerEvent('chatstate', 'onChatState', 'chat');
        $this->registerEvent('message', 'onMessage');
        $this->registerEvent('displayed', 'onDisplayed', 'chat');
        $this->registerEvent('presence_unavailable_handle', 'onDisconnected', 'chat');

        $this->registerEvent('presence_muc_handle', 'onConnected'/*, 'chat'*/);
        $this->registerEvent('presence_muc_errorconflict', 'onConflict');
        $this->registerEvent('presence_muc_errorregistrationrequired', 'onRegistrationRequired');
        $this->registerEvent('presence_muc_errorremoteservernotfound', 'onRemoteServerNotFound');
        $this->registerEvent('presence_muc_errorremoteservertimeout', 'onRemoteServerTimeout');
        $this->registerEvent('presence_muc_erroritemnotfound', 'onItemNotFound');
        $this->registerEvent('presence_muc_errornotauthorized', 'onNotAuthorized');
        $this->registerEvent('presence_muc_errorforbidden', 'onForbidden');
        $this->registerEvent('presence_muc_errorjidmalformed', 'onJidMalformed');
        $this->registerEvent('presence_muc_errornotacceptable', 'onNotAcceptable');
        $this->registerEvent('presence_muc_errorserviceunavailable', 'onServiceUnavailable');

        $this->registerEvent('presence_muji', 'onMujiPresence');
        $this->registerEvent('presence_was_muji', 'onMujiPresence');
        $this->registerEvent('presence_muc_muji_leaving', 'onMujiPresence');
    }

    public function onDiscoRequest(Packet $packet)
    {
        $info = $packet->content;

        if ($info->isConference()) {
            $this->ajaxHttpGet();

            if ($info->hasMAM()) {
                $message = $this->me->messages()
                    ->where('jidfrom', $info->server)
                    ->whereNull('subject');

                $message = (DB::getDriverName() == 'pgsql')
                    ? $message->orderByRaw('published desc nulls last')
                    : $message->orderBy('published', 'desc');
                $message = $message->first();

                $g = new \Moxl\Xec\Action\MAM\Get($this->me, sessionId: $this->sessionId);
                $g->setTo($info->server)
                    ->setLimit(500);

                if (
                    !empty($message)
                    && strtotime($message->published) > strtotime('-3 days')
                ) {
                    $g->setStart(strtotime($message->published));
                } else {
                    $g->setStart(strtotime('-3 days'));
                }

                $this->rpc('MovimUtils.addClass', '#chat_widget .contained', 'loading');

                $g->request();
            }
        }
    }

    public function onChatState(Packet $packet)
    {
        $this->rpc(
            !empty($packet->content)
                ? 'MovimUtils.addClass'
                : 'MovimUtils.removeClass',
            '#' . cleanupId($packet->from . '_rooms_primary'),
            'composing'
        );
    }

    public function onMujiPresence(Packet $packet)
    {
        $presence = $packet->content;
        $this->onPresence($presence->jid);
    }

    public function onMessage(Packet $packet)
    {
        $message = $packet->content;

        if ($message->isMuc()) {
            $chatStates = linker($this->sessionId)->chatStates;
            $chatStates->clearState($message->jidfrom, $message->resource);

            $this->onChatState($chatStates->getState($message->jidfrom));
            $this->setCounter($message->jidfrom);
        }
    }

    public function onDisplayed(Packet $packet)
    {
        $message = $packet->content;

        if ($message->isMuc() && $message->jidto == $this->me->id) {
            $this->onPresence($message->jidfrom);
        }
    }

    public function onDestroyed(Packet $packet)
    {
        $this->ajaxHttpGet();
        $this->rpc('Chat_ajaxGet');

        $this->toast($this->__('chatrooms.destroyed'));
    }

    public function onConnected(Packet $packet)
    {
        $this->onPresence($packet->content->jid);
    }

    public function onDisconnected(Packet $packet)
    {
        if ($packet->content) {
            $this->onPresence($packet->content);
            $this->toast($this->__('chatrooms.disconnected'));
        }
    }

    public function onBookmarkGet(Packet $packet)
    {
        foreach (
            $this->me->session->conferences()
                ->fromSpace(false)
                ->with('info')
                ->where('bookmarkversion', (int)$packet->content)
                ->get() as $room
        ) {
            if (!$room->info) {
                $jid = explodeJid($room->conference);

                $request = $this->xmpp(new Request);
                $request->setTo($room->conference)
                    ->setParent($jid['server'])
                    ->request();
            }

            if ($room->autojoin && !$room->connected) {
                $this->ajaxJoin($room->conference, $room->nick);
            }
        }

        $this->ajaxHttpGet();
    }

    public function onBookmarkRetract(Packet $packet)
    {
        $this->ajaxHttpGet();
    }

    public function onBookmarkSet(Packet $packet)
    {
        $conference = $packet->content;

        if (!$conference || $conference->isFromSpace()) return;

        if ($conference && $conference->autojoin) {
            $this->ajaxJoin($conference->conference, $conference->nick);
        }

        $this->toast($this->__('bookmarks.updated'));

        if ($conference) {
            $this->onPresence($conference->conference);
        } else {
            $this->rpc('Rooms_ajaxHttpGet');
        }
    }

    public function setCounter(string $room)
    {
        $conference = $this->me->session
            ->conferences()
            ->where('conference', $room)
            ->withCount('unreads', 'quoted')
            ->first();

        if ($conference) {
            if ($conference->isFromSpace()) {
                Wrapper::getInstance()->iterate(
                    'space_counter',
                    (new Packet)->pack(
                        $this->me->unreads(space: [$conference->space_server, $conference->space_node]),
                        $conference->spaceCounterId
                    ),
                    user: $this->me,
                    sessionId: $this->sessionId
                );
            }

            $this->rpc(
                'MovimTpl.fill',
                '#' . cleanupId($room . '_rooms_primary'),
                $this->prepareRoomCounter($conference, $conference->getPicture())
            );

            $unread = ($conference->unreads_count > 0 || $conference->quoted_count > 0);
            $this->rpc('Rooms.setUnread', cleanupId($room), $unread);
        }
    }

    public function onPresence(string $room)
    {
        $conference = $this->me->session->conferences()
            ->fromSpace(false)
            ->where('conference', $room)
            ->with('info', 'contact', 'presence')
            ->withCount('unreads', 'quoted', 'presences')
            ->first();

        if ($conference) {
            $this->rpc('Rooms.setRoom', \cleanupId($conference->conference), $this->prepareConference($conference));
            $this->rpc('Rooms.refresh');
        }
    }

    public function ajaxRefresh(string $room)
    {
        $this->onPresence($room);
    }

    public function ajaxHttpGet()
    {
        $conferences = $this->me->session->conferences()
            ->fromSpace(false)
            ->with('info', 'contact', 'presence')
            ->withCount('unreads', 'quoted', 'presences')
            ->get();

        $this->rpc('Rooms.clearRooms');

        foreach ($conferences as $conference) {
            $this->rpc('Rooms.setRoom', \cleanupId($conference->conference), $this->prepareConference($conference));
        }

        $this->rpc('Rooms.refresh');
        $this->rpc('Rooms.checkNoConnected');

        $this->rpc('MovimUtils.removeClass', '#rooms ul.list.rooms', 'spin');

        $view = $this->tpl();
        $this->rpc('MovimTpl.remove', '#rooms ul.list.empty');
        $this->rpc('MovimTpl.appendAfter', '#rooms ul.list.rooms', $view->draw('_rooms_empty'));
    }

    /**
     * @brief Join a chatroom
     */
    public function ajaxJoin(string $room, ?string $nickname = null)
    {
        if (!validateJid($room)) {
            return;
        }

        $this->rpc('MovimUtils.addClass', '#' . \cleanupId($room), 'connecting');

        $jid = explodeJid($room);

        $r = $this->xmpp(new Request);
        $r->setTo($room)
            ->setParent($jid['server'])
            ->request();

        $lastMember = Member::where('conference', $room)->orderBy('updated_at', 'desc')->first();

        $p = $this->xmpp(new Muc);
        $p->setTo($room);
        $p->setNickname($nickname ?? $this->me->username);

        if ($lastMember && $lastMember->version) {
            $p->setMavsince($lastMember->version);
        }

        $p->request();
    }

    /**
     * @brief Exit a room
     *
     * @param string $room
     */
    public function ajaxExit($room)
    {
        if (!validateJid($room)) {
            return;
        }

        // We reset the Chat view
        $this->rpc('Chat_ajaxGet');

        // We properly exit
        $conference = $this->me->session->conferences()
            ->where('conference', $room)
            ->first();

        if (!$conference) return;

        $resource = $conference->presence?->resource;

        $jid = explodeJid($room);
        $capability = \App\Info::where('server', $jid['server'])
            ->where('node', '')
            ->first();

        if (!$capability || !$capability->hasMAM()) {
            $this->me->messages()->where('jidfrom', $room)->delete();
        }

        // We clear the ping timer
        linker($this->sessionId)->chatroomPings->clear($room);

        // We clear the presences from the buffer cache and then the DB
        $this->me->session->conferences()
            ->where('conference', $room)
            ->first()->presences()->delete();

        $this->ajaxHttpGet();

        if ($resource) {
            linker($this->sessionId)->session->delete($room . '/' . $resource);

            $pu = $this->xmpp(new Unavailable);
            $pu->setTo($room)
                ->setResource($resource)
                ->request();
        }
    }

    public function prepareConference(Conference $conference)
    {
        $view = $this->tpl();
        $view->assign('conference', $conference);

        return $view->draw('_rooms_room');
    }

    public function prepareRoomCounter(Conference $conference, $withAvatar = false)
    {
        $view = $this->tpl();
        $view->assign('conference', $conference);
        $view->assign('withAvatar', $withAvatar);

        return $view->draw('_rooms_counter');
    }

    /**
     * Join errors
     */

    public function onConflict()
    {
        $this->toast($this->__('chatrooms.conflict'));
    }

    public function onRegistrationRequired(Packet $packet)
    {
        $this->toast($this->__('chatrooms.registrationrequired'));
        $this->ajaxExit($packet->content);
    }

    public function onRemoteServerNotFound(Packet $packet)
    {
        $this->toast($this->__('chatrooms.remoteservernotfound'));
        $this->ajaxExit($packet->content);
    }

    public function onRemoteServerTimeout(Packet $packet)
    {
        $this->toast($this->__('chatrooms.remoteservertimeout'));
        $this->ajaxExit($packet->content);
    }

    public function onItemNotFound(Packet $packet)
    {
        $this->toast($this->__('chatrooms.itemnotfound'));
        $this->ajaxExit($packet->content);
    }

    public function onNotAuthorized(Packet $packet)
    {
        $this->toast($this->__('chatrooms.notauthorized'));
        $this->ajaxExit($packet->content);
    }

    public function onForbidden(Packet $packet)
    {
        $this->toast($this->__('chatrooms.forbidden'));
        $this->ajaxExit($packet->content);
    }

    public function onJidMalformed(Packet $packet)
    {
        $this->toast($this->__('chatrooms.jidmalformed'));
        $this->ajaxExit($packet->content);
    }

    public function onNotAcceptable(Packet $packet)
    {
        $this->toast($this->__('chatrooms.notacceptable'));
        $this->ajaxExit($packet->content);
    }

    public function onServiceUnavailable(Packet $packet)
    {
        $this->toast($this->__('chatrooms.serviceunavailable'));
        $this->ajaxExit($packet->content);
    }
}
