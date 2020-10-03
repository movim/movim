<?php

use Moxl\Xec\Action\Vcard\Set as VcardSet;
use Moxl\Xec\Action\Message\Invite;
use Moxl\Xec\Action\Muc\SetSubject;
use Moxl\Xec\Action\Muc\Destroy;
use Moxl\Xec\Action\Disco\Items;
use Moxl\Xec\Action\Bookmark2\Set;
use Moxl\Xec\Action\Bookmark2\Delete;
use Moxl\Xec\Action\Bookmark\Synchronize;

use Movim\Widget\Base;
use Movim\Picture;

use App\Conference;
use App\Info;

use Respect\Validation\Validator;
use Cocur\Slugify\Slugify;

class RoomsUtils extends Base
{
    public function load()
    {
        $this->registerEvent('vcard_set_handle', 'onAvatarSet', 'chat');
        $this->registerEvent('disco_items_nosave_handle', 'onDiscoGateway');
        $this->registerEvent('disco_items_nosave_error', 'onDiscoGatewayError');
    }

    /**
     * Display the room subject
     */
    public function ajaxShowSubject($room = false)
    {
        if (!$this->validateRoom($room)) {
            return;
        }

        $conference = $this->user->session->conferences()
            ->where('conference', $room)
            ->with('info')
            ->first();

        if (!$conference) return;

        $view = $this->tpl();
        $view->assign('conference', $conference);
        $view->assign('room', $room);
        $view->assign('list', $conference->presences()
            ->with('capability')
            ->get());
        $view->assign('me', $this->user->id);

        Drawer::fill($view->draw('_rooms_drawer'));
    }

        /**
     * @brief Get the avatar form
     */
    public function ajaxGetAvatar($room)
    {
        if (!$this->validateRoom($room)) {
            return;
        }

        $view = $this->tpl();
        $view->assign('room', $this->user->session->conferences()
                                 ->where('conference', $room)
                                 ->first());

        Dialog::fill($view->draw('_rooms_avatar'));
    }

    /**
     * @brief Set the avatar
     */
    public function ajaxSetAvatar($room, $form)
    {
        if (!$this->validateRoom($room)) {
            return;
        }

        $p = new Picture;
        $p->fromBase($form->photobin->value);

        $p->set('temp', 'jpeg', 60);

        $p = new Picture;
        $p->get('temp');

        $vcard = new stdClass;
        $vcard->photobin = new stdClass;
        $vcard->phototype = new stdClass;
        $vcard->photobin->value = $p->toBase();
        $vcard->phototype->value = 'image/jpeg';

        $r = new VcardSet;
        $r->setData($vcard)->setTo($room)->request();
    }

    public function onAvatarSet($packet)
    {
        $this->rpc('Dialog_ajaxClear');
        Toast::send($this->__('avatar.updated'));
    }

    /**
     * @brief Get the subject form of a chatroom
     */
    public function ajaxGetSubject($room)
    {
        if (!$this->validateRoom($room)) {
            return;
        }

        $view = $this->tpl();
        $view->assign('room', $this->user->session->conferences()
                                 ->where('conference', $room)
                                 ->first());

        Dialog::fill($view->draw('_rooms_subject'));
        $this->rpc('MovimUtils.applyAutoheight');
    }

    /**
     * @brief Change the subject of a chatroom
     */
    public function ajaxSetSubject($room, $form)
    {
        if (!$this->validateRoom($room)
        || !Validator::stringType()->length(0, 200)->validate($form->subject->value)) {
            return;
        }

        $p = new SetSubject;
        $p->setTo($room)
          ->setSubject($form->subject->value)
          ->request();
    }

    /**
     * @brief Display the add room form
     */
    public function ajaxAskInvite($room = false)
    {
        $view = $this->tpl();

        $view->assign('contacts', $this->user->session->contacts()->pluck('jid'));
        $view->assign('room', $room);
        $view->assign('invite', \App\Invite::set($this->user->id, $room));

        Dialog::fill($view->draw('_rooms_invite'));
    }

    /**
     * @brief Display the add room form
     */
    public function ajaxAdd($room = false, $name = null)
    {
        $view = $this->tpl();

        $view->assign('info', \App\Info::where('server', $room)
                                       ->where('node', '')
                                       ->whereCategory('conference')
                                       ->first());
        $view->assign('mucservice', \App\Info::where('parent', $this->user->session->host)
                                             ->whereCategory('conference')
                                             ->whereType('text')
                                             ->first());
        $view->assign('id', $room);
        $view->assign(
            'conference',
            $this->user->session->conferences()
            ->where('conference', $room)->first()
        );
        $view->assign('name', $name);
        $view->assign('username', $this->user->session->username);
        $view->assign(
            'gateways',
            \App\Info::whereIn('server', function ($query) {
                $query->select('jid')->from('presences');
            })
            ->whereCategory('gateway')
            ->get()
        );

        $this->rpc('Rooms.setDefaultServices', $this->user->session->getChatroomsServices());

        Dialog::fill($view->draw('_rooms_add'));
    }

    /**
     * Resolve the room slug from the name
     */
    public function ajaxResolveSlug($name)
    {
        $service = Info::where('parent', $this->user->session->host)
                   ->whereCategory('conference')
                   ->whereType('text')
                   ->first();

        $slugified = (new Slugify)->slugify($name);

        if ($service && !empty($slugified)) {
            $this->rpc('Rooms.setJid', $slugified.'@'. $service->server);
        }
    }

    /**
     * @brief Confirm the room add
     */
    public function ajaxAddConfirm($form)
    {
        if (!$this->validateRoom($form->jid->value)) {
            Toast::send($this->__('chatrooms.bad_id'));
        } elseif (trim($form->name->value) == '') {
            Toast::send($this->__('chatrooms.empty_name'));
        } else {
            $this->rpc('Rooms_ajaxExit', $form->jid->value);

            $conference = $this->user->session->conferences()
                 ->where('conference', strtolower($form->jid->value))
                 ->first();

            if (!$conference) $conference = new Conference;

            $conference->conference = strtolower($form->jid->value);
            $conference->name = $form->name->value;
            $conference->autojoin = $form->autojoin->value;
            $conference->nick = $form->nick->value;
            $conference->notify = (int)array_flip(Conference::$notifications)[$form->notify->value];

            $conferenceSave = clone $conference;
            $conference->delete();

            $b = new Set;
            $b->setConference($conferenceSave)
              ->request();

            $this->rpc('Dialog_ajaxClear');
        }
    }

    /**
     * @brief Display the remove room confirmation
     */
    public function ajaxRemove($room)
    {
        if (!$this->validateRoom($room)) {
            return;
        }

        $view = $this->tpl();
        $view->assign('room', $room);

        Dialog::fill($view->draw('_rooms_remove'));
    }

    /**
     * @brief Remove a room
     */
    public function ajaxRemoveConfirm($room)
    {
        if (!$this->validateRoom($room)) {
            return;
        }

        $conference = $this->user->session->conferences()
            ->where('conference', strtolower($room))
            ->first();

        $d = new Delete;
        $d->setId($room)
          ->setVersion($conference->bookmarkversion)
          ->request();
    }

    /**
     * @brief Invite someone to a room
     */
    public function ajaxInvite($form)
    {
        if (!$this->validateRoom($form->to->value)) {
            return;
        }

        if (!empty($form->invite->value)) {
            $i = new Invite;
            $i->setTo($form->to->value)
              ->setId(generateUUID())
              ->setInvite($form->invite->value)
              ->request();

            Toast::send($this->__('room.invited'));
            $this->rpc('Dialog_ajaxClear');
        }
    }

    /**
     * @brief Destroy a room
     */
    public function ajaxAskDestroy($room)
    {
        if (!$this->validateRoom($room)) {
            return;
        }

        $view = $this->tpl();
        $view->assign('room', $room);

        Dialog::fill($view->draw('_rooms_destroy'));
    }

    /**
     * @brief Destroy a room
     */
    public function ajaxDestroy($room)
    {
        if (!$this->validateRoom($room)) {
            return;
        }

        $d = new Destroy;
        $d->setTo($room)
          ->request();
    }

    /**
     * @brief Autocomplete users in MUC
     */
    public function ajaxMucUsersAutocomplete($room)
    {
        $this->rpc("Chat.onAutocomplete", $this->user->session->conferences()
                                               ->where('conference', $room)
                                               ->first()->presences
                                               ->pluck('resource'));
    }

    /**
     * Synchronize Bookmark 1 to Bookmark 2
     */
    public function ajaxSyncBookmark()
    {
        $s = new Synchronize;
        $s->request();
    }

    /**
     * @brief Discover rooms for a gateway
     */
    public function ajaxDiscoGateway(string $server)
    {
        if (empty($server)) {
            $this->ajaxResetGatewayRooms();
            $this->rpc('Rooms.selectGatewayRoom', '', '');
        } else {
            $r = new Items;
            $r->setTo($server)
              ->disableSave()
              ->request();
        }
    }

    public function onDiscoGateway($packet)
    {
        $view = $this->tpl();
        $view->assign('rooms', $packet->content);
        $this->rpc('MovimTpl.fill', '#gateway_rooms', $view->draw('_rooms_gateway_rooms'));
    }

    public function onDiscoGatewayError($packet)
    {
        $this->ajaxResetGatewayRooms();
    }

    public function ajaxResetGatewayRooms()
    {
        $this->rpc('MovimTpl.fill', '#gateway_rooms', '');
    }

    /**
     * @brief Validate the room
     *
     * @param string $room
     */
    private function validateRoom($room)
    {
        return (Validator::stringType()->noWhitespace()->contains('@')->length(6, 256)->validate($room));
    }
}