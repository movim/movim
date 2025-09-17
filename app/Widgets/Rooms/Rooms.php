<?php

namespace App\Widgets\Rooms;

use Moxl\Xec\Action\Disco\Request;
use Moxl\Xec\Action\Presence\Muc;
use Moxl\Xec\Action\Presence\Unavailable;
use Moxl\Xec\Action\Muc\GetMembers;

use Movim\Session;
use Movim\ChatStates;
use Movim\Widget\Base;

use App\Conference;
use App\Widgets\Notif\Notif;
use App\Widgets\Toast\Toast;
use Movim\ChatroomPings;
use Moxl\Xec\Payload\Packet;

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

        $this->registerEvent('disco_request_handle', 'onDiscoRequest', 'chat');

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

        $this->registerEvent('callinvitepropose', 'onCallInvitePropose');
        $this->registerEvent('callinviteaccept', 'onCallInvite');
        $this->registerEvent('callinviteleft', 'onCallInvite');
        $this->registerEvent('callinviteretract', 'onCallInvite');
        $this->registerEvent('presence_muji_event', 'onCallInvite');
    }

    public function onDiscoRequest(Packet $packet)
    {
        $info = $packet->content;

        if ($info->isConference()) {
            $this->ajaxHttpGet();
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

    public function onCallInvitePropose(Packet $packet)
    {
        $muji = $packet->content;

        if ($muji->jidfrom && $muji->conference && !$muji->inviter->me) {
            Notif::append(
                'chat|' . $muji->jidfrom,
                ($muji->conference != null && $muji->conference->name)
                    ? $muji->conference->name
                    : $muji->jidfrom,
                ($muji->video)
                    ? "📹 " . __('muji.call_video_invite')
                    : "📞 " . __('muji.call_audio_invite'),
                $muji->conference->getPicture(),
                5,
                $this->route('chat', [$muji->jidfrom, 'room']),
                null,
                'Search.chat(\'' . echapJS($muji->jidfrom) . '\', true)'
            );

            $this->onCallInvite($packet);
        }
    }

    public function onCallInvite(Packet $packet)
    {
        $muji = $packet->content;

        if ($muji->jidfrom && $muji->conference) {
            $this->onPresence($muji->jidfrom);
        }
    }

    public function onMessage($packet)
    {
        $message = $packet->content;

        if ($message->isMuc()) {
            $chatStates = ChatStates::getInstance();
            $chatStates->clearState($message->jidfrom, $message->resource);

            $this->onChatState($chatStates->getState($message->jidfrom));
            $this->setCounter($message->jidfrom);
        }
    }

    public function onDisplayed($packet)
    {
        $message = $packet->content;

        if ($message->isMuc() && $message->jidto == $this->me->id) {
            $this->onPresence($message->jidfrom);
        }
    }

    public function onDestroyed($packet)
    {
        $this->ajaxHttpGet();
        $this->rpc('Chat_ajaxGet');

        Toast::send($this->__('chatrooms.destroyed'));
    }

    public function onConnected($packet)
    {
        list($presence, $notify) = array_values($packet->content);
        $this->onPresence($presence->jid);
    }

    public function onDisconnected($packet)
    {
        if ($packet->content) {
            $this->onPresence($packet->content);
            Toast::send($this->__('chatrooms.disconnected'));
        }
    }

    public function onBookmarkGet($packet)
    {
        foreach (
            $this->me->session->conferences()
                ->with('info')
                ->where('bookmarkversion', (int)$packet->content)
                ->get() as $room
        ) {
            if (!$room->info) {
                $jid = explodeJid($room->conference);

                $request = new Request;
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

    public function onBookmarkRetract($packet)
    {
        $this->ajaxHttpGet();
    }

    public function onBookmarkSet($packet)
    {
        $conference = $packet->content;

        if ($conference && $conference->autojoin) {
            $this->ajaxJoin($conference->conference, $conference->nick);
        }

        Toast::send($this->__('bookmarks.updated'));

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
            $this->rpc(
                'MovimTpl.fill',
                '#' . cleanupId($room . '_rooms_primary'),
                $this->prepareRoomCounter($conference, $conference->getPicture())
            );

            $unread = ($conference->unreads_count > 0 || $conference->quoted_count > 0);
            $this->rpc('Rooms.setUnread', cleanupId($room), $unread);
        }
    }

    public function onPresence(string $room, bool $callSecond = true)
    {
        $conference = $this->me->session->conferences()
            ->where('conference', $room)
            ->with('info', 'contact', 'presence')
            ->withCount('unreads', 'quoted', 'presences')
            ->first();

        if ($conference) {
            $this->rpc('Rooms.setRoom', \cleanupId($conference->conference), $this->prepareConference($conference), $callSecond);
            $this->rpc('Rooms.refresh', $callSecond);
        }
    }

    public function ajaxSecondGet(string $room)
    {
        $this->onPresence($room, callSecond: false);
    }

    public function ajaxHttpGet()
    {
        $conferences = $this->me->session->conferences()
            ->with('info', 'contact', 'presence')
            ->withCount('unreads', 'quoted', 'presences')
            ->get();

        $this->rpc('Rooms.clearRooms');

        foreach ($conferences as $conference) {
            $this->rpc('Rooms.setRoom', \cleanupId($conference->conference), $this->prepareConference($conference));
        }

        $this->rpc('Rooms.refresh', true);
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
        if (!validateRoom($room)) {
            return;
        }

        $jid = explodeJid($room);

        $r = new Request;
        $r->setTo($room)
            ->setParent($jid['server'])
            ->request();

        $p = new Muc;
        $p->setTo($room);

        if ($nickname == null) {
            $nickname = $this->me->username;
        }

        $capability = \App\Info::where('server', $jid['server'])
            ->where('node', '')
            ->first();

        if ($capability && ($capability->isMAM() || $capability->isMAM2())) {
            $this->rpc('MovimUtils.addClass', '#chat_widget .contained', 'loading');

            $p->enableMAM();

            if ($capability->isMAM2()) {
                $p->enableMAM2();
            }
        }

        $m = new GetMembers;
        $m->setTo($room)
            ->request();

        $p->setNickname($nickname);
        $p->request();
    }

    /**
     * @brief Exit a room
     *
     * @param string $room
     */
    public function ajaxExit($room)
    {
        if (!validateRoom($room)) {
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

        if (!$capability || !$capability->isMAM()) {
            $this->me->messages()->where('jidfrom', $room)->delete();
        }

        // We clear the ping timer
        ChatroomPings::getInstance()->clear($room);

        // We clear the presences from the buffer cache and then the DB
        $this->me->session->conferences()
            ->where('conference', $room)
            ->first()->presences()->delete();

        $this->ajaxHttpGet();

        if ($resource) {
            $session = Session::instance();
            $session->delete($room . '/' . $resource);

            $pu = new Unavailable;
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
        Toast::send($this->__('chatrooms.conflict'));
    }

    public function onRegistrationRequired($packet)
    {
        Toast::send($this->__('chatrooms.registrationrequired'));
        $this->ajaxExit($packet->content);
    }

    public function onRemoteServerNotFound($packet)
    {
        Toast::send($this->__('chatrooms.remoteservernotfound'));
        $this->ajaxExit($packet->content);
    }

    public function onRemoteServerTimeout($packet)
    {
        Toast::send($this->__('chatrooms.remoteservertimeout'));
        $this->ajaxExit($packet->content);
    }

    public function onItemNotFound($packet)
    {
        Toast::send($this->__('chatrooms.itemnotfound'));
        $this->ajaxExit($packet->content);
    }

    public function onNotAuthorized($packet)
    {
        Toast::send($this->__('chatrooms.notauthorized'));
        $this->ajaxExit($packet->content);
    }

    public function onForbidden($packet)
    {
        Toast::send($this->__('chatrooms.forbidden'));
        $this->ajaxExit($packet->content);
    }

    public function onJidMalformed($packet)
    {
        Toast::send($this->__('chatrooms.jidmalformed'));
        $this->ajaxExit($packet->content);
    }

    public function onNotAcceptable($packet)
    {
        Toast::send($this->__('chatrooms.notacceptable'));
        $this->ajaxExit($packet->content);
    }

    public function onServiceUnavailable($packet)
    {
        Toast::send($this->__('chatrooms.serviceunavailable'));
        $this->ajaxExit($packet->content);
    }
}
