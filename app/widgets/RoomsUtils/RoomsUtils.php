<?php

use Moxl\Xec\Action\Vcard\Set as VcardSet;
use Moxl\Xec\Action\Message\Invite;
use Moxl\Xec\Action\Muc\SetSubject;
use Moxl\Xec\Action\Muc\Destroy;
use Moxl\Xec\Action\Muc\CreateChannel;
use Moxl\Xec\Action\Muc\CreateGroupChat;
use Moxl\Xec\Action\Disco\Items;
use Moxl\Xec\Action\Bookmark2\Set;
use Moxl\Xec\Action\Bookmark2\Delete;
use Moxl\Xec\Action\Bookmark\Synchronize;
use Moxl\Xec\Payload\Packet;

use Movim\Widget\Base;
use Movim\Picture;

use App\Conference;
use App\Info;

use Respect\Validation\Validator;
use Cocur\Slugify\Slugify;
use Movim\EmbedLight;

include_once WIDGETS_PATH.'Chat/Chat.php';

class RoomsUtils extends Base
{
    private $_picturesPagination = 20;
    private $_linksPagination = 12;

    public function load()
    {
        $this->registerEvent('vcard_set_handle', 'onAvatarSet', 'chat');
        $this->registerEvent('disco_items_nosave_handle', 'onDiscoGateway');
        $this->registerEvent('disco_items_nosave_error', 'onDiscoGatewayError');
        $this->registerEvent('muc_creategroupchat_handle', 'onChatroomCreated');
        $this->registerEvent('muc_createchannel_handle', 'onChatroomCreated');

        $this->addjs('roomsutils.js');
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

        $picturesCount = $conference->pictures()->count();
        $linksCount = $conference->links()->count();

        $view = $this->tpl();
        $view->assign('conference', $conference);
        $view->assign('room', $room);
        $view->assign('picturesCount', $picturesCount);
        $view->assign('linksCount', $linksCount);

        $view->assign('presences', $conference->presences()
             ->with('capability')
             ->get());

        if ($conference->isGroupChat()) {
            $view->assign('members', $conference->members()
                 ->with('contact')
                 ->get());
        }

        $view->assign('me', $this->user->id);

        Drawer::fill($view->draw('_rooms_drawer'));
        $this->rpc('Tabs.create');

        if ($picturesCount > 0) {
            $this->rpc('RoomsUtils_ajaxHttpGetPictures', $room);
        }

        if ($linksCount > 0) {
            $this->rpc('RoomsUtils_ajaxHttpGetLinks', $room);
        }
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
     * @brief If a chatroom is successfuly created
     */
    public function onChatroomCreated($packet)
    {
        $values = $packet->content;

        $conference = $this->user->session->conferences()
            ->where('conference', $values['jid'])
            ->first();

        if (!$conference) $conference = new Conference;

        $conference->conference = $values['jid'];
        $conference->name = $values['name'];
        $conference->autojoin = $values['autojoin'];
        $conference->pinned = $values['pinned'];
        $conference->nick = $values['nick'];
        $conference->notify = $values['notify'];

        $conferenceSave = clone $conference;
        $conference->delete();

        $b = new Set;
        $b->setConference($conferenceSave)
            ->request();

        $this->rpc('Dialog_ajaxClear');
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
    public function ajaxAdd($room = false, $name = null, $create = false)
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
        $view->assign('create', $create);
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
     * @brief Create a chatroom
     */
    public function ajaxAddCreate($form)
    {
        if (!$this->validateRoom($form->jid->value)) {
            Toast::send($this->__('chatrooms.bad_id'));
        } elseif (trim($form->name->value) == '') {
            Toast::send($this->__('chatrooms.empty_name'));
        } else {
            if ($form->type->value == 'groupchat') {
                $cgc = new CreateGroupChat;
                $cgc->setTo(strtolower($form->jid->value))
                    ->setName($form->name->value)
                    ->setAutoJoin($form->autojoin->value)
                    ->setPinned($form->pinned->value)
                    ->setNick($form->nick->value)
                    ->setNotify((int)array_flip(Conference::$notifications)[$form->notify->value])
                    ->request();
            } elseif ($form->type->value == 'channel') {
                $cgc = new CreateChannel;
                $cgc->setTo(strtolower($form->jid->value))
                    ->setName($form->name->value)
                    ->setAutoJoin($form->autojoin->value)
                    ->setPinned($form->pinned->value)
                    ->setNick($form->nick->value)
                    ->setNotify((int)array_flip(Conference::$notifications)[$form->notify->value])
                    ->request();
            }
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

            $packet = new Packet;
            $packet->content = [
                'jid' => $form->jid->value,
                'name' => $form->name->value,
                'nick' => $form->nick->value,
                'autojoin' => $form->autojoin->value,
                'pinned' => $form->pinned->value,
                'notify' => (int)array_flip(Conference::$notifications)[$form->notify->value],
            ];

            $this->onChatroomCreated($packet);
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
     * @brief Get a page of pictures in the drawer
     */
    public function ajaxHttpGetPictures($room, $page = 0)
    {
        if (!$this->validateRoom($room)) {
            return;
        }

        $conference = $this->user->session->conferences()
            ->where('conference', $room)
            ->with('info')
            ->first();

        if (!$conference) return;

        $tpl = $this->tpl();

        $more = false;
        $pictures = $conference->pictures()
            ->take($this->_picturesPagination + 1)
            ->skip($this->_picturesPagination * $page)
            ->get();

        if ($pictures->count() == $this->_picturesPagination + 1) {
            $pictures->pop();
            $more = true;
        }
        $tpl->assign('pictures', $pictures);
        $tpl->assign('more', $more);
        $tpl->assign('page', $page);
        $tpl->assign('room', $room);

        $this->rpc('MovimTpl.append', '#room_pictures', $tpl->draw('_rooms_drawer_pictures'));
    }

    /**
     * @brief Get a page of links in the drawer
     */
    public function ajaxHttpGetLinks($room, $page = 0)
    {
        if (!$this->validateRoom($room)) {
            return;
        }

        $conference = $this->user->session->conferences()
            ->where('conference', $room)
            ->with('info')
            ->first();

        if (!$conference) return;

        $tpl = $this->tpl();

        $more = false;
        $links = $conference->links()
            ->take($this->_linksPagination + 1)
            ->skip($this->_linksPagination * $page)
            ->get();

        if ($links->count() == $this->_linksPagination + 1) {
            $links->pop();
            $more = true;
        }
        $tpl->assign('links', $links);
        $tpl->assign('more', $more);
        $tpl->assign('page', $page);
        $tpl->assign('room', $room);

        $this->rpc('MovimTpl.append', '#room_links', $tpl->draw('_rooms_drawer_links'));
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

    public function prepareEmbedUrl(EmbedLight $embed)
    {
        return (new Chat)->prepareEmbed($embed, true);
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