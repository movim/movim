<?php

namespace App\Widgets\RoomsUtils;

use Moxl\Xec\Action\Vcard\Set as VcardSet;
use Moxl\Xec\Action\Message\Invite;
use Moxl\Xec\Action\Muc\SetSubject;
use Moxl\Xec\Action\Muc\Destroy;
use Moxl\Xec\Action\Muc\CreateChannel;
use Moxl\Xec\Action\Muc\CreateGroupChat;
use Moxl\Xec\Action\Disco\Items;
use Moxl\Xec\Action\Bookmark2\Set;
use Moxl\Xec\Action\Bookmark2\Delete;
use Moxl\Xec\Payload\Packet;

use Movim\Widget\Base;
use Movim\Image;

use App\Conference;
use App\Contact;
use App\Info;
use App\Message;
use App\Widgets\AdHoc\AdHoc;
use App\Widgets\Chat\Chat;
use App\Widgets\Chats\Chats;
use App\Widgets\Dialog\Dialog;
use App\Widgets\Drawer\Drawer;
use App\Widgets\Toast\Toast;
use Respect\Validation\Validator;
use Movim\Session;
use Moxl\Xec\Action\Muc\ChangeAffiliation;
use Moxl\Xec\Action\Presence\Muc;
use Moxl\Xec\Action\Presence\Unavailable;

use Illuminate\Database\Capsule\Manager as DB;
use Moxl\Xec\Action\Register\Remove;

class RoomsUtils extends Base
{
    private $_picturesPagination = 20;
    private $_linksPagination = 12;

    public function load()
    {
        $this->registerEvent('vcard_set_handle', 'onAvatarSet', 'chat');
        $this->registerEvent('disco_items_nosave_handle', 'onDiscoGateway');
        $this->registerEvent('disco_items_nosave_error', 'onDiscoGatewayError');
        $this->registerEvent('disco_items_errorregistrationrequired', 'onDiscoRegistrationRequired');
        $this->registerEvent('muc_creategroupchat_handle', 'onChatroomCreated');
        $this->registerEvent('muc_createchannel_handle', 'onChatroomCreated');
        $this->registerEvent('muc_creategroupchat_error', 'onChatroomCreatedError');
        $this->registerEvent('muc_createchannel_error', 'onChatroomCreatedError');
        $this->registerEvent('muc_changeaffiliation_handle', 'onAffiliationChanged');
        $this->registerEvent('muc_changeaffiliation_errornotallowed', 'onAffiliationChangeUnauthorized');
        $this->registerEvent('message_invite_error', 'onInviteError');

        $this->registerEvent('presence_muc_create_handle', 'onMucCreated');
        $this->registerEvent('presence_muc_errornotallowed', 'onPresenceMucNotAllowed');
        $this->registerEvent('presence_muc_errorgone', 'onPresenceMucNotAllowed');

        $this->addjs('roomsutils.js');
        $this->addcss('roomsutils.css');
    }

    public function ajaxGetDrawer($room = false)
    {
        if (!validateRoom($room)) {
            return;
        }

        $conference = $this->me->session->conferences()
            ->where('conference', $room)
            ->with('info')
            ->first();

        if (!$conference) return;

        $picturesCount = $conference->pictures()->count();
        $linksCount = $conference->links()->count();

        $presences = $conference->presences()->with('capability')->get();

        $view = $this->tpl();
        $view->assign('conference', $conference);
        $view->assign('room', $room);
        $view->assign('picturesCount', $picturesCount);
        $view->assign('linksCount', $linksCount);
        $view->assign('presences', $presences);

        if ($conference->isGroupChat()) {
            $view->assign('members', $conference->activeMembers()
                ->whereNotIn('jid', $presences->pluck('mucjid'))
                ->with('contact')
                ->get());
        }

        $view->assign('banned', $conference->members()
            ->with('contact')
            ->where('affiliation', '=', 'outcast')
            ->get());

        $view->assign('me', $this->me->id);

        Drawer::fill('room_drawer', $view->draw('_rooms_drawer'));
        $this->rpc('Tabs.create');

        $this->rpc('RoomsUtils_ajaxAppendPresences', $room, 0, !$conference->isGroupChat());

        if ($picturesCount > 0) {
            $this->rpc('RoomsUtils_ajaxHttpGetPictures', $room);
        }

        if ($linksCount > 0) {
            $this->rpc('RoomsUtils_ajaxHttpGetLinks', $room);
        }

        if ($this->me->hasOMEMO() && $conference->isGroupChat()) {
            $this->rpc(
                'RoomsUtils.getDrawerFingerprints',
                $room,
                $conference->members()->whereNot('jid', $this->me->id)->get()->pluck('jid')->toArray()
            );
        } else {
            $tpl = $this->tpl();
            $this->rpc('MovimTpl.fill', '#room_omemo_fingerprints', $tpl->draw('_rooms_drawer_fingerprints_placeholder'));
        }

        (new AdHoc)->ajaxGet($room);
    }

    public function ajaxAppendPresences($room, int $page = 0, bool $havePagination = true)
    {
        $pagination = 20;

        $conference = $this->me->session->conferences()
            ->where('conference', $room)
            ->with('info')
            ->first();

        if (!$conference) return;

        $presences = $conference->presences()
            ->with('capability')
            ->skip($page * $pagination);

        if ($havePagination) {
            $presences = $presences->take($pagination + 1);
        }

        $presences = $presences->get();

        if ($havePagination && $presences->count() == $pagination + 1) {
            $presences->pop();
        }

        $tpl = $this->tpl();
        $tpl->assign('more', $havePagination);
        $tpl->assign('conference', $conference);
        $tpl->assign('presences', $presences);
        $tpl->assign('page', $page + 1);
        $tpl->assign('me', $this->me->id);

        $this->rpc('MovimTpl.remove', '#room_presences_more');
        $this->rpc('MovimTpl.append', '#room_presences_list', $tpl->draw('_rooms_presences_list'));
    }

    public function ajaxGetDrawerFingerprints($room, $contactsFingerprints)
    {
        $resolvedFingerprints = collect();

        foreach ($contactsFingerprints as $contactFingerprints) {
            if (!empty($contactFingerprints)) {
                foreach ($contactFingerprints as $fingerprint) {
                    $fingerprint->fingerprint = base64ToFingerPrint($fingerprint->fingerprint);
                }

                $resolvedFingerprints->put($contactFingerprints[0]->jid, $contactFingerprints);
            }
        }

        $tpl = $this->tpl();
        $tpl->assign('fingerprints', $resolvedFingerprints);
        $tpl->assign('clienttype', getClientTypes());
        $tpl->assign('contacts', Contact::whereIn('id', $resolvedFingerprints->keys())->get()->keyBy('id'));

        $this->rpc('MovimTpl.fill', '#room_omemo_fingerprints', $tpl->draw('_rooms_drawer_fingerprints'));
        foreach ($resolvedFingerprints as $jid => $value) {
            $this->rpc('ContactActions.resolveSessionsStates', $jid, true);
        }
        $this->rpc('RoomsUtils.resolveRoomEncryptionState', $room);
    }

    /**
     * @brief Get the avatar form
     */
    public function ajaxGetAvatar($room)
    {
        if (!validateRoom($room)) {
            return;
        }

        $view = $this->tpl();
        $view->assign('room', $this->me->session->conferences()
            ->where('conference', $room)
            ->first());

        Dialog::fill($view->draw('_rooms_avatar'));
    }

    /**
     * @brief Set the avatar
     */
    public function ajaxSetAvatar($room, $form)
    {
        if (!validateRoom($room)) {
            return;
        }

        $tempKey = \generateKey(6);

        $p = new Image;
        $p->fromBase($form->photobin->value);
        $p->setKey($tempKey);
        $p->save(false, false, 'jpeg', 60);

        // Reload
        $p->load('jpeg');

        $vcard = new \stdClass;
        $vcard->photobin = new \stdClass;
        $vcard->phototype = new \stdClass;
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

    public function onMucCreated($packet)
    {
        $this->rpc('RoomsUtils.configureCreatedRoom');
    }

    public function onPresenceMucNotAllowed($packet)
    {
        Toast::send($this->__('chatrooms.notallowed'));
    }

    public function onDiscoRegistrationRequired($packet)
    {
        Toast::send($this->__('rooms.disco_registration_required'));
    }

    /**
     * @brief Affiliation changed for a user
     */
    public function onAffiliationChanged($packet)
    {
        $affiliation = $packet->content;

        switch ($affiliation) {
            case 'owner':
                Toast::send($this->__('room.affiliation_owner_changed'));
                break;

            case 'admin':
                Toast::send($this->__('room.affiliation_admin_changed'));
                break;

            case 'member':
                Toast::send($this->__('room.affiliation_member_changed'));
                break;

            case 'outcast':
                Toast::send($this->__('room.affiliation_outcast_changed'));
                break;

            case 'none':
                Toast::send($this->__('room.affiliation_none_changed'));
                break;
        }
    }

    /**
     * @brief When the affiliation change is unauthorized
     */
    public function onAffiliationChangeUnauthorized($packet)
    {
        Toast::send($this->__('room.change_affiliation_unauthorized'));
    }

    /**
     * @brief If a chatroom is successfuly created
     */
    public function onChatroomCreated($packet)
    {
        Toast::send($this->__('chatrooms.created'));

        $values = $packet->content;

        $conference = $this->me->session->conferences()
            ->where('conference', $values['jid'])
            ->firstOrNew();

        $conference->conference = $values['jid'];
        $conference->name = $values['name'];
        $conference->autojoin = $values['autojoin'];
        $conference->pinned = $values['pinned'];
        $conference->nick = $values['nick'];
        $conference->notify = $values['notify'];

        $b = new Set;
        $b->setConference($conference)
            ->request();

        // Disconnect properly
        $nick = $values['nick'] ?? $this->me->username;
        $session = Session::instance();
        $session->delete($values['jid'] . '/' . $nick);

        $pu = new Unavailable;
        $pu->setTo($values['jid'])
            ->setResource($nick)
            ->request();

        $this->me->session->presences()->where('jid', $values['jid'])->delete();
        //$this->rpc('RoomsUtils.configureDisconnect', $values['jid']);

        $this->rpc('Dialog_ajaxClear');
    }

    /**
     * @brief If a chatroom creation is failing
     */
    public function onChatroomCreatedError($packet)
    {
        Toast::send($packet->content);
    }

    /**
     * @brief Get the subject form of a chatroom
     */
    public function ajaxGetSubject($room)
    {
        if (!validateRoom($room)) {
            return;
        }

        $view = $this->tpl();
        $view->assign('room', $this->me->session->conferences()
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
        if (
            !validateRoom($room)
            || !Validator::stringType()->length(0, 200)->isValid($form->subject->value)
        ) {
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

        $view->assign('contacts', $this->me->session->contacts()->pluck('jid'));
        $view->assign('room', $room);
        $view->assign('invite', \App\Invite::set($this->me->id, $room));

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
        $view->assign('mucservice', \App\Info::where('parent', $this->me->session->host)
            ->whereCategory('conference')
            ->whereType('text')
            ->first());
        $view->assign('id', $room);
        $view->assign('create', $create);
        $view->assign(
            'conference',
            $this->me->session->conferences()
                ->where('conference', $room)->first()
        );
        $view->assign('name', $name);
        $view->assign('username', $this->me->username);

        $gateways = \App\Info::select('name', 'server', 'parent')
            ->whereCategory('gateway')
            ->whereNotNull('parent')
            ->groupBy('name', 'server', 'parent')
            ->orderBy('parent')
            ->orderBy('server')
            ->get();

        $gateways = $gateways->filter(fn($gateway) => $gateway->parent === $this->me->session->host)
            ->concat($gateways->reject(fn($gateway) => $gateway->parent === $this->me->session->host));

        $view->assign('gateways', $gateways);

        $this->rpc('Rooms.setDefaultServices', $this->me->session->getChatroomsServices());

        Dialog::fill($view->draw('_rooms_add'));
    }

    /**
     * Resolve the room slug from the name
     */
    public function ajaxResolveSlug($name)
    {
        $service = Info::where('parent', $this->me->session->host)
            ->whereCategory('conference')
            ->whereType('text')
            ->first();

        $slugified = slugify($name);

        if ($service && !empty($slugified)) {
            $this->rpc('Rooms.setJid', $slugified . '@' . $service->server);
        }
    }

    /**
     * @brief Create a chatroom
     */
    public function ajaxAddCreate($form)
    {
        if (!validateRoom($form->jid->value)) {
            Toast::send($this->__('chatrooms.bad_id'));
        } elseif (trim($form->name->value) == '') {
            Toast::send($this->__('chatrooms.empty_name'));
        } else {
            $m = new Muc;
            $m->enableCreate()
                ->noNotify()
                ->setTo(strtolower($form->jid->value))
                ->setNickname($form->nick->value ?? $this->me->username)
                ->request();
        }
    }

    public function ajaxConfigureCreated($form)
    {
        if (!validateRoom($form->jid->value)) {
            Toast::send($this->__('chatrooms.bad_id'));
        } else {
            if ($form->type->value == 'groupchat') {
                $cgc = new CreateGroupChat;
                $cgc->setTo(strtolower($form->jid->value))
                    ->setName($form->name->value)
                    ->setAutoJoin($form->autojoin->value)
                    ->setPinned($form->pinned->value)
                    ->setNick($form->nick->value ?? $this->me->username)
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
        if (!validateRoom($form->jid->value)) {
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
        if (!validateRoom($room)) {
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
        if (!validateRoom($room)) {
            return;
        }

        $conference = $this->me->session->conferences()
            ->where('conference', strtolower($room))
            ->first();

        $d = new Delete;
        $d->setId($room)
            ->setVersion($conference->bookmarkversion)
            ->request();

        $unregister = new Remove;
        $unregister->setTo($room)
            ->request();
    }

    /**
     * @brief Invite someone to a room
     */
    public function ajaxInvite($form)
    {
        if (!validateRoom($form->to->value)) {
            return;
        }

        if (!empty($form->invite->value)) {
            $id = generateUUID();
            $i = new Invite;
            $i->setTo($form->to->value)
                ->setId($id)
                ->setInvite($form->invite->value)
                ->request();

            // Create and save a message in the database to display the invitation
            $m = new Message;

            $m->user_id = $this->me->id;
            $m->id = $id;
            $m->type = 'invitation';
            $m->subject = $form->to->value;
            $m->jidfrom = $this->me->id;
            $m->jidto = $form->invite->value;
            $m->published = gmdate('Y-m-d H:i:s');
            $m->body = '';
            $m->markable = true;
            $m->seen = true;
            $m->resource = $this->me->session->resource;
            $m->save();

            $m = $m->fresh();

            $packet = new \Moxl\Xec\Payload\Packet;
            $packet->content = $m;

            (new Chats())->onMessage($packet);
            (new Chat())->onMessage($packet);

            // Notify
            Toast::send($this->__('room.invited'));
            $this->rpc('Dialog_ajaxClear');
        }
    }

    public function onInviteError($packet)
    {
        Toast::send($packet->content);
    }

    /**
     * @brief Destroy a room
     */
    public function ajaxAskDestroy($room)
    {
        if (!validateRoom($room)) {
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
        if (!validateRoom($room)) {
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
        if (!validateRoom($room)) {
            return;
        }

        $conference = $this->me->session->conferences()
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
        if (!validateRoom($room)) {
            return;
        }

        $conference = $this->me->session->conferences()
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
        $this->rpc("Chat.onAutocomplete", $this->me->session->conferences()
            ->where('conference', $room)
            ->first()->presences
            ->pluck('resource'));
    }

    /**
     * @brief Discover rooms for a gateway
     */
    public function ajaxDiscoGateway(string $server)
    {
        $this->ajaxResetGatewayRooms();
        $this->rpc('Rooms.selectGatewayRoom', '', '');

        if (!empty($server)) {
            $r = new Items;
            $r->setTo($server)
                ->disableSave()
                ->request();
        }
    }

    /**
     * @brief Show the ban form
     */
    public function ajaxAddBanned(string $room)
    {
        if (!validateRoom($room)) {
            return;
        }

        $view = $this->tpl();
        $view->assign('room', $this->me->session->conferences()
            ->where('conference', $room)
            ->first());

        Dialog::fill($view->draw('_rooms_ban'));
    }

    /**
     * @brief Ban someone
     */
    public function ajaxAddBannedConfirm(string $room, $form)
    {
        if (!validateRoom($room)) {
            return;
        }

        $p = new ChangeAffiliation;
        $p = $p->setTo($room)
            ->setJid($form->jid->value)
            ->setAffiliation('outcast');

        if (!empty($form->reason->value)) {
            $p = $p->setReason($form->reason->value);
        }

        $p->request();
    }

    /**
     * @brief Show the unban form
     */
    public function ajaxRemoveBanned(string $room, string $jid)
    {
        if (!validateRoom($room)) {
            return;
        }

        $view = $this->tpl();
        $view->assign('room', $this->me->session->conferences()
            ->where('conference', $room)
            ->first());
        $view->assign('jid', $jid);

        Dialog::fill($view->draw('_rooms_unban'));
    }

    /**
     * @brief Unban someone
     */
    public function ajaxRemoveBannedConfirm(string $room, string $jid)
    {
        if (!validateRoom($room)) {
            return;
        }

        $p = new ChangeAffiliation;
        $p->setTo($room)
            ->setJid($jid)
            ->setAffiliation('none')
            ->request();
    }

    /**
     * @brief Show the change affiliation form
     */
    public function ajaxChangeAffiliation(string $room, string $jid)
    {
        if (!validateRoom($room)) {
            return;
        }

        $conference = $this->me->session->conferences()
            ->where('conference', $room)
            ->first();

        $view = $this->tpl();
        $view->assign('room', $conference);
        $view->assign('member', $conference->members()->where('jid', $jid)->first());
        $view->assign('jid', $jid);

        Dialog::fill($view->draw('_rooms_change_affiliation'));
    }

    /**
     * @brief Change the affiliation
     */
    public function ajaxChangeAffiliationConfirm(string $room, $form)
    {
        if (!validateRoom($room)) {
            return;
        }

        $p = new ChangeAffiliation;
        $p->setTo($room)
            ->setJid($form->jid->value)
            ->setAffiliation($form->affiliation->value)
            ->request();
    }

    /**
     * @brief Get MAM history
     */
    public function ajaxGetMAMHistory(string $jid)
    {
        $g = new \Moxl\Xec\Action\MAM\Get;

        $message = $this->me->messages()
            ->where('jidfrom', $jid)
            ->whereNull('subject');

        $message = (DB::getDriverName() == 'pgsql')
            ? $message->orderByRaw('published asc nulls last')
            : $message->orderBy('published', 'asc');
        $message = $message->first();

        $g->setTo($jid);

        if ($message && $message->published) {
            $g->setEnd(strtotime($message->published));
        }

        $g->setLimit(150);
        $g->setBefore('');
        $g->request();
    }

    public function onDiscoGateway($packet)
    {
        $view = $this->tpl();

        $groups = [];

        $rooms = collect($packet->content);
        $rooms = $rooms->map(function ($name, $key) use (&$groups) {
            $item = new \stdClass;
            $explodedName = explode('/', $name);

            if (count($explodedName) > 1) {
                $item->parent = $explodedName[0];
                array_shift($explodedName);
                $item->name = implode(' / ', $explodedName);

                if (!array_key_exists($item->parent, $groups)) {
                    $groups[$item->parent] = 0;
                }

                $groups[$item->parent]++;
            } else {
                $item->parent = null;
                $item->name = $name;
            }

            return $item;
        })->map(function ($item, $key) use ($groups) {
            if ($item->parent != null && array_key_exists($item->parent, $groups) && $groups[$item->parent] == 1) {
                $item->name = $item->parent . '/' . $item->name;
                $item->parent = null;
            }

            return $item;
        })->sortBy('parent');

        $view->assign('rooms', $rooms);
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

    public function prepareEmbedUrl(Message $message)
    {
        $resolved = $message->resolvedUrl->cache;

        if ($resolved) {
            return (new Chat())->prepareEmbed($resolved, $message);
        }
    }
}
